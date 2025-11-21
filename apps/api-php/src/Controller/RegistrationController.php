<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegistrationController extends AbstractController
{
    #[OA\Post(
        path: '/api/register',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', minLength: 6, example: 'password123'),
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER'])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'User registered successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data or validation errors',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Invalid data')
                            ]
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string'))
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    #[Route('/api/register', name: 'app_api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $content = $request->getContent();

        // Check for empty body first
        if (empty($content)) {
            return new JsonResponse(['error' => 'Empty request body'], 400);
        }

        $data = json_decode($content, true);

        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse([
                'error' => 'Invalid JSON: ' . json_last_error_msg()
            ], 400);
        }

        if (!$data) {
            return new JsonResponse(['error' => 'Request body must be a JSON object'], 400);
        }

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'email and password required'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        // Set roles, default to ROLE_USER
        $roles = $data['roles'] ?? ['ROLE_USER'];
        $user->setRoles($roles);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse([
                'error' => 'Email already exists'
            ], 400);
        } catch (\Exception $e) {
            // Re-throw to be handled by global exception handler
            throw $e;
        }

        return new JsonResponse(['message' => 'User registered successfully'], 201);
    }
}
