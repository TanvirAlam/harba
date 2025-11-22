<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Repository\ProviderRepository;
use App\Repository\ServiceRepository;
use App\Service\BookingService;
use App\Service\SlotGeneratorService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BookingController extends AbstractController
{
    #[Route('/api/bookings/available-slots', name: 'api_bookings_available_slots', methods: ['GET'])]
    public function availableSlots(
        Request $request,
        ProviderRepository $providerRepository,
        ServiceRepository $serviceRepository,
        SlotGeneratorService $slotGenerator
    ): JsonResponse {
        $providerId = $request->query->get('provider_id');
        $serviceId = $request->query->get('service_id');

        if (!$providerId || !$serviceId) {
            return new JsonResponse(['error' => 'provider_id and service_id required'], 400);
        }

        $provider = $providerRepository->find($providerId);
        $service = $serviceRepository->find($serviceId);

        if (!$provider || !$service) {
            return new JsonResponse(['error' => 'Invalid provider or service'], 404);
        }

        $slots = $slotGenerator->generateAvailableSlots($provider, $service);
        return new JsonResponse($slots);
    }

    #[Route('/api/bookings', name: 'api_bookings_book', methods: ['POST'])]
    public function book(
        Request $request,
        EntityManagerInterface $entityManager,
        BookingService $bookingService
    ): JsonResponse {
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

        if (!isset($data['provider_id'], $data['service_id'], $data['datetime'])) {
            return new JsonResponse(['error' => 'provider_id, service_id, datetime required'], 400);
        }

        $provider = $entityManager->getRepository(Provider::class)->find($data['provider_id']);
        $service = $entityManager->getRepository(Service::class)->find($data['service_id']);

        // Validate booking data
        $errors = $bookingService->validateBookingData($provider, $service, $data['datetime']);
        if (!empty($errors)) {
            return new JsonResponse(['error' => $errors[0]], 400);
        }

        // Parse datetime (already validated in service)
        $datetime = new \DateTime($data['datetime']);

        // Create booking using service
        try {
            $bookingService->createBooking($this->getUser(), $provider, $service, $datetime);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 409);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse([
                'error' => 'Slot already booked (race condition detected)'
            ], 409);
        } catch (\Exception $e) {
            // Re-throw to be handled by global exception handler
            throw $e;
        }

        return new JsonResponse(['message' => 'Booking created'], 201);
    }

    #[Route('/api/bookings/{id}', name: 'api_bookings_cancel', methods: ['DELETE'])]
    public function cancel(
        int $id,
        EntityManagerInterface $entityManager,
        BookingService $bookingService
    ): JsonResponse {
        $booking = $entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return new JsonResponse(['error' => 'Booking not found'], 404);
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$bookingService->canUserCancelBooking($booking, $this->getUser(), $isAdmin)) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        try {
            $bookingService->cancelBooking($booking);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse(['message' => 'Booking cancelled']);
    }

    #[Route('/api/bookings/{id}/hard-delete', name: 'api_bookings_hard_delete', methods: ['DELETE'])]
    public function hardDelete(
        int $id,
        EntityManagerInterface $entityManager,
        BookingService $bookingService
    ): JsonResponse {
        // Disable soft delete filter to find even soft-deleted bookings
        $filters = $entityManager->getFilters();
        if ($filters->isEnabled('softdeleteable')) {
            $filters->disable('softdeleteable');
        }

        $booking = $entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return new JsonResponse(['error' => 'Booking not found'], 404);
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$bookingService->canUserCancelBooking($booking, $this->getUser(), $isAdmin)) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        try {
            $bookingService->hardDeleteBooking($booking);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse(['message' => 'Booking permanently deleted']);
    }

    #[Route('/api/bookings/my', name: 'api_bookings_my', methods: ['GET'])]
    public function myBookings(BookingService $bookingService): JsonResponse
    {
        $bookings = $bookingService->getUserBookings($this->getUser());

        $result = [];
        foreach ($bookings as $booking) {
            $result[] = [
                'id' => $booking->getId(),
                'provider' => $booking->getProvider()->getName(),
                'service' => $booking->getService()->getName(),
                'datetime' => $booking->getDatetime()->format('Y-m-d H:i:s'),
                'user' => $booking->getUser()->getEmail(),
                'status' => $booking->getStatus(),
            ];
        }

        return new JsonResponse($result);
    }

    #[Route('/api/bookings/all', name: 'api_bookings_all', methods: ['GET'])]
    public function allBookings(BookingService $bookingService): JsonResponse
    {
        $bookings = $bookingService->getAllBookings();

        $result = [];
        foreach ($bookings as $booking) {
            $result[] = [
                'id' => $booking->getId(),
                'provider' => $booking->getProvider()->getName(),
                'service' => $booking->getService()->getName(),
                'datetime' => $booking->getDatetime()->format('Y-m-d H:i:s'),
                'user' => $booking->getUser()->getEmail(),
                'status' => $booking->getStatus(),
            ];
        }

        return new JsonResponse($result);
    }

}
