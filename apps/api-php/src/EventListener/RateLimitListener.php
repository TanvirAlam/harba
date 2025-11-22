<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitListener
{
    public function __construct(
        private RateLimiterFactory $loginLimiter,
        private RateLimiterFactory $registrationLimiter,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Apply rate limiting to login endpoint
        if ($path === '/api/login_check' && $request->isMethod('POST')) {
            $limiter = $this->loginLimiter->create($request->getClientIp());
            $limit = $limiter->consume();

            if (!$limit->isAccepted()) {
                $event->setResponse(new JsonResponse([
                    'error' => 'Too many login attempts. Please try again later.',
                    'retry_after' => $limit->getRetryAfter()->getTimestamp(),
                ], 429));
                return;
            }
        }

        // Apply rate limiting to registration endpoint
        if ($path === '/api/register' && $request->isMethod('POST')) {
            $limiter = $this->registrationLimiter->create($request->getClientIp());
            $limit = $limiter->consume();

            if (!$limit->isAccepted()) {
                $event->setResponse(new JsonResponse([
                    'error' => 'Too many registration attempts. Please try again later.',
                    'retry_after' => $limit->getRetryAfter()->getTimestamp(),
                ], 429));
                return;
            }
        }
    }
}
