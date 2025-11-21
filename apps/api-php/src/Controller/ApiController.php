<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
    #[Route('/api/login_check', name: 'app_api_login_check', methods: ['POST'])]
    public function login(Request $request, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository): JsonResponse
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
        
        if (!isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'username and password required'], 400);
        }
        $username = $data['username'];
        $password = $data['password'];
        $user = $userRepository->findOneBy(['email' => $username]);
        if (!$user || !password_verify($password, $user->getPassword())) {
            return new JsonResponse(['message' => 'Invalid credentials'], 401);
        }
        $token = $jwtManager->create($user);
        return new JsonResponse(['token' => $token]);
    }

    #[OA\Get(
        path: '/api/profile',
        summary: 'Get user profile',
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER'])
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    #[Route('/api/profile', name: 'app_api_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        $user = $this->getUser();
        return new JsonResponse([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }
}
