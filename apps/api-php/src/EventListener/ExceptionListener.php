<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function __construct(
        private LoggerInterface $logger,
        private string $environment
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        // Log the exception
        $this->logger->error('Exception occurred: ' . $exception->getMessage(), [
            'exception' => $exception,
            'trace' => $exception->getTraceAsString(),
        ]);

        // Handle database unique constraint violations
        if ($this->isUniqueConstraintViolation($exception)) {
            $response = new JsonResponse([
                'error' => 'Resource already exists or conflict detected'
            ], 409);
            $event->setResponse($response);
            return;
        }

        // Create JSON response
        $statusCode = 500;
        $message = 'An unexpected error occurred';

        // Handle HTTP exceptions (thrown by Symfony)
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        } elseif ($this->environment === 'dev') {
            // In development, show actual error message
            $message = $exception->getMessage();
        }

        $responseData = ['error' => $message];

        // In development, include exception details
        if ($this->environment === 'dev') {
            $responseData['exception'] = get_class($exception);
            $responseData['file'] = $exception->getFile();
            $responseData['line'] = $exception->getLine();
        }

        $response = new JsonResponse($responseData, $statusCode);
        $event->setResponse($response);
    }

    private function isUniqueConstraintViolation(\Throwable $exception): bool
    {
        // Check if it's directly a unique constraint violation
        if ($exception instanceof UniqueConstraintViolationException) {
            return true;
        }

        // Check if the previous exception is a unique constraint violation
        $previous = $exception->getPrevious();
        while ($previous !== null) {
            if ($previous instanceof UniqueConstraintViolationException) {
                return true;
            }
            $previous = $previous->getPrevious();
        }

        // Check exception message for unique constraint keywords
        $message = strtolower($exception->getMessage());
        if (str_contains($message, 'unique constraint') || 
            str_contains($message, 'duplicate entry') ||
            str_contains($message, 'duplicate key')) {
            return true;
        }

        return false;
    }
}
