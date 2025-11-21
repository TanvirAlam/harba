<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Repository\BookingRepository;
use App\Repository\ProviderRepository;
use App\Repository\ServiceRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
class BookingController extends AbstractController
{
    #[Route('/api/bookings/available-slots', name: 'api_bookings_available_slots', methods: ['GET'])]
    public function availableSlots(Request $request, ProviderRepository $providerRepository, ServiceRepository $serviceRepository, BookingRepository $bookingRepository): JsonResponse
    {
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

        $slots = $this->generateAvailableSlots($provider, $service, $bookingRepository);
        return new JsonResponse($slots);
    }

    #[Route('/api/bookings', name: 'api_bookings_book', methods: ['POST'])]
    public function book(Request $request, EntityManagerInterface $entityManager): JsonResponse
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

        if (!isset($data['provider_id'], $data['service_id'], $data['datetime'])) {
            return new JsonResponse(['error' => 'provider_id, service_id, datetime required'], 400);
        }

        $provider = $entityManager->getRepository(Provider::class)->find($data['provider_id']);
        $service = $entityManager->getRepository(Service::class)->find($data['service_id']);

        if (!$provider || !$service) {
            return new JsonResponse(['error' => 'Invalid provider or service'], 404);
        }

        // Parse and validate datetime
        try {
            $datetime = new \DateTime($data['datetime']);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Invalid datetime format. Expected format: YYYY-MM-DD HH:MM:SS'
            ], 400);
        }

        // Validate booking is within provider's working hours
        $dayOfWeek = $datetime->format('l');
        $workingHours = $provider->getWorkingHours();

        if (!isset($workingHours[$dayOfWeek]) || empty($workingHours[$dayOfWeek])) {
            return new JsonResponse([
                'error' => 'Provider does not work on ' . $dayOfWeek
            ], 400);
        }

        // Parse working hours and validate booking time
        if (preg_match('/^(\d{2}:\d{2})-(\d{2}:\d{2})$/', $workingHours[$dayOfWeek], $matches)) {
            $startTime = \DateTime::createFromFormat('Y-m-d H:i', 
                                                    $datetime->format('Y-m-d') . ' ' . $matches[1]);
            $endTime = \DateTime::createFromFormat('Y-m-d H:i', 
                                                  $datetime->format('Y-m-d') . ' ' . $matches[2]);
            
            // Check if booking time is within working hours
            if ($datetime < $startTime || $datetime >= $endTime) {
                return new JsonResponse([
                    'error' => 'Booking time is outside provider working hours (' . $matches[1] . '-' . $matches[2] . ')'
                ], 400);
            }
            
            // Check if service duration fits within working hours
            $serviceEndTime = clone $datetime;
            $serviceEndTime->modify("+{$service->getDuration()} minutes");
            
            if ($serviceEndTime > $endTime) {
                return new JsonResponse([
                    'error' => 'Service duration extends beyond provider closing time'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'error' => 'Invalid working hours format for provider'
            ], 400);
        }

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

        try {
            $entityManager->persist($booking);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            // Handle race condition where slot was booked between availability check and save
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
    public function myBookings(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $bookings = $entityManager->getRepository(Booking::class)->findBy(['user' => $user]);

        $result = [];
        foreach ($bookings as $booking) {
            $result[] = [
                'id' => $booking->getId(),
                'provider' => $booking->getProvider()->getName(),
                'service' => $booking->getService()->getName(),
                'datetime' => $booking->getDatetime()->format('Y-m-d H:i:s'),
                'user' => $booking->getUser()->getEmail(),
            ];
        }

        return new JsonResponse($result);
    }

    #[Route('/api/bookings/all', name: 'api_bookings_all', methods: ['GET'])]
    public function allBookings(EntityManagerInterface $entityManager): JsonResponse
    {
        $bookings = $entityManager->getRepository(Booking::class)->findAll();

        $result = [];
        foreach ($bookings as $booking) {
            $result[] = [
                'id' => $booking->getId(),
                'provider' => $booking->getProvider()->getName(),
                'service' => $booking->getService()->getName(),
                'datetime' => $booking->getDatetime()->format('Y-m-d H:i:s'),
                'user' => $booking->getUser()->getEmail(),
            ];
        }

        return new JsonResponse($result);
    }

    private function generateAvailableSlots(Provider $provider, Service $service, BookingRepository $bookingRepository): array
    {
        $slots = [];
        $workingHours = $provider->getWorkingHours();
        $serviceDuration = $service->getDuration();
        
        // Generate slots for the next 30 days
        $startDate = new \DateTime('today');
        $endDate = (new \DateTime('today'))->modify('+30 days');
        
        // Get all existing bookings for this provider in the date range
        $existingBookings = $bookingRepository->createQueryBuilder('b')
            ->where('b.provider = :provider')
            ->andWhere('b.datetime >= :startDate')
            ->andWhere('b.datetime <= :endDate')
            ->setParameter('provider', $provider)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
        
        // Create a set of booked datetimes for quick lookup
        $bookedSlots = [];
        foreach ($existingBookings as $booking) {
            $bookedSlots[$booking->getDatetime()->format('Y-m-d H:i:s')] = true;
        }
        
        // Iterate through each day in the range
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->format('l'); // Monday, Tuesday, etc.
            
            // Check if provider works on this day
            if (isset($workingHours[$dayOfWeek]) && !empty($workingHours[$dayOfWeek])) {
                $hours = $workingHours[$dayOfWeek];
                
                // Parse working hours (e.g., "09:00-17:00")
                if (preg_match('/^(\d{2}:\d{2})-(\d{2}:\d{2})$/', $hours, $matches)) {
                    $startTime = $matches[1];
                    $endTime = $matches[2];
                    
                    // Create datetime objects for this day's working hours
                    $slotTime = \DateTime::createFromFormat('Y-m-d H:i', $currentDate->format('Y-m-d') . ' ' . $startTime);
                    $dayEndTime = \DateTime::createFromFormat('Y-m-d H:i', $currentDate->format('Y-m-d') . ' ' . $endTime);
                    
                    // Generate 30-minute slots
                    while ($slotTime < $dayEndTime) {
                        // Check if slot has enough time for the service
                        $slotEndTime = clone $slotTime;
                        $slotEndTime->modify("+{$serviceDuration} minutes");
                        
                        // Only add slot if service fits within working hours
                        if ($slotEndTime <= $dayEndTime) {
                            $slotString = $slotTime->format('Y-m-d H:i:s');
                            
                            // Only add if not already booked
                            if (!isset($bookedSlots[$slotString])) {
                                $slots[] = $slotString;
                            }
                        }
                        
                        // Move to next 30-minute slot
                        $slotTime->modify('+30 minutes');
                    }
                }
            }
            
            // Move to next day
            $currentDate->modify('+1 day');
        }
        
        return $slots;
    }
}