<?php

namespace App\Controller;

use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ServiceController extends AbstractController
{
    #[Route('/api/services', name: 'api_services_list', methods: ['GET'])]
    public function list(ServiceRepository $serviceRepository): JsonResponse
    {
        $services = $serviceRepository->findAll();
        $data = array_map(fn($service) => [
            'id' => $service->getId(),
            'name' => $service->getName(),
            'duration' => $service->getDuration(),
        ], $services);

        return new JsonResponse($data);
    }
}