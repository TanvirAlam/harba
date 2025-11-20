<?php

namespace App\Controller;

use App\Repository\ProviderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProviderController extends AbstractController
{
    #[Route('/api/providers', name: 'api_providers_list', methods: ['GET'])]
    public function list(ProviderRepository $providerRepository): JsonResponse
    {
        $providers = $providerRepository->findAll();
        $data = array_map(fn($provider) => [
            'id' => $provider->getId(),
            'name' => $provider->getName(),
            'workingHours' => $provider->getWorkingHours(),
        ], $providers);

        return new JsonResponse($data);
    }
}