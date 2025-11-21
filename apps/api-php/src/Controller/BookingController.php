<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Repository\BookingRepository;
use App\Repository\ProviderRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BookingController extends AbstractController
{
    #[Route('/api/bookings/available-slots', name: 'api_bookings_available_slots', methods: ['GET'])]
    public function availableSlots(Request $request, ProviderRepository $providerRepository, BookingRepository $bookingRepository): JsonResponse
    {
        $providerId = $request->query->get('provider_id');
        $serviceId = $request->query->get('service_id');

        if (!$providerId || !$serviceId) {
            return new JsonResponse(['error' => 'provider_id and service_id required'], 400);
        }

        $provider = $providerRepository->find($providerId);
        $service = $this->getDoctrine()->getRepository(Service::class)->find($serviceId);

        if (!$provider || !$service) {
            return new JsonResponse(['error' => 'Invalid provider or service'], 404);
        }

        $slots = $this->generateAvailableSlots($provider, $service, $bookingRepository);

        return new JsonResponse($slots);
    }

    #[Route('/api/bookings', name: 'api_bookings_book', methods: ['POST'])]
    public function book(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['provider_id'], $data['service_id'], $data['datetime'])) {
            return new JsonResponse(['error' => 'provider_id, service_id, datetime required'], 400);
        }

        $provider = $entityManager->getRepository(Provider::class)->find($data['provider_id']);
        $service = $entityManager->getRepository(Service::class)->find($data['service_id']);

        if (!$provider || !$service) {
            return new JsonResponse(['error' => 'Invalid provider or service'], 404);
        }

        $datetime = new \DateTime($data['datetime']);

        // Check if slot is available
        $existing = $entityManager->getRepository(Booking::class)->findOneBy([
            'provider' => $provider,
            'datetime' => $datetime,
        ]);

        if ($existing) {
            return new JsonResponse(['error' => 'Slot already booked'], 409);
        }

        $booking = new Booking();
        $booking->setUser($this->getUser());
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime($datetime);

        $entityManager->persist($booking);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Booking created'], 201);
    }

    #[Route('/api/bookings/{id}', name: 'api_bookings_cancel', methods: ['DELETE'])]
    public function cancel(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $booking = $entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return new JsonResponse(['error' => 'Booking not found'], 404);
        }

        if ($booking->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        $entityManager->remove($booking);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Booking cancelled']);
    }

    #[Route('/api/bookings/my', name: 'api_bookings_my', methods: ['GET'])]
    public function myBookings(): JsonResponse
    {
        $bookings = $this->getDoctrine()->getRepository(Booking::class)->findBy(['user' => $this->getUser()]);
        $data = array_map(fn($booking) => [
            'id' => $booking->getId(),
            'provider' => $booking->getProvider()->getName(),
            'service' => $booking->getService()->getName(),
            'datetime' => $booking->getDatetime()->format('Y-m-d H:i:s'),
        ], $bookings);

        return new JsonResponse($data);
    }

    #[Route('/api/bookings/all', name: 'api_bookings_all', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function allBookings(): JsonResponse
    {
        $bookings = $this->getDoctrine()->getRepository(Booking::class)->findAll();
        $data = array_map(fn($booking) => [
            'id' => $booking->getId(),
            'user' => $booking->getUser()->getEmail(),
            'provider' => $booking->getProvider()->getName(),
            'service' => $booking->getService()->getName(),
            'datetime' => $booking->getDatetime()->format('Y-m-d H:i:s'),
        ], $bookings);

        return new JsonResponse($data);
    }

    private function generateAvailableSlots(Provider $provider, Service $service, BookingRepository $bookingRepository): array
    {
        $slots = [];
        $now = new \DateTime();
        $endDate = (clone $now)->modify('+30 days');

        $current = clone $now;
        while ($current <= $endDate) {
            $dayOfWeek = strtolower($current->format('l'));
            $workingHours = $provider->getWorkingHours()[$dayOfWeek] ?? null;

            if ($workingHours && $workingHours !== 'closed' && strpos($workingHours, '-') !== false) {
                [$start, $end] = explode('-', $workingHours);
                $startTime = \DateTime::createFromFormat('H:i', $start, $current->getTimezone());
                $endTime = \DateTime::createFromFormat('H:i', $end, $current->getTimezone());

                $startTime->setDate($current->format('Y'), $current->format('m'), $current->format('d'));
                $endTime->setDate($current->format('Y'), $current->format('m'), $current->format('d'));

                $slotTime = clone $startTime;
                while ($slotTime < $endTime) {
                    $slotEnd = clone $slotTime;
                    $slotEnd->modify('+' . $service->getDuration() . ' minutes');

                    if ($slotEnd <= $endTime) {
                        $slots[] = $slotTime->format('Y-m-d H:i:s');
                    }

                    $slotTime->modify('+30 minutes');
                }
            }

            $current->modify('+1 day');
        }

        // Remove booked slots
        $bookings = $bookingRepository->findBookingsForProviderBetweenDates($provider, $now, $endDate);
        $bookedTimes = array_map(fn($b) => $b->getDatetime()->format('Y-m-d H:i:s'), $bookings);
        $slots = array_diff($slots, $bookedTimes);

        return array_values($slots);
    }
}