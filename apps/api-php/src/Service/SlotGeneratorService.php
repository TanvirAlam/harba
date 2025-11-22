<?php

namespace App\Service;

use App\Entity\Provider;
use App\Entity\Service;
use App\Repository\BookingRepository;

class SlotGeneratorService
{
    private const SLOT_INTERVAL_MINUTES = 30;
    private const DEFAULT_DAYS_AHEAD = 30;

    public function __construct(
        private BookingRepository $bookingRepository,
        private WorkingHoursValidator $workingHoursValidator
    ) {
    }

    /**
     * Generates available time slots for a provider and service.
     * 
     * @return array<string> Array of datetime strings in 'Y-m-d H:i:s' format
     */
    public function generateAvailableSlots(
        Provider $provider,
        Service $service,
        int $daysAhead = self::DEFAULT_DAYS_AHEAD
    ): array {
        $slots = [];
        $serviceDuration = $service->getDuration();
        
        // Define date range
        $startDate = new \DateTime('today');
        $endDate = (new \DateTime('today'))->modify("+{$daysAhead} days");
        
        // Get existing confirmed bookings for this provider in the date range
        $bookedSlots = $this->getBookedSlots($provider, $startDate, $endDate);
        
        // Generate slots for each day in the range
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dailySlots = $this->generateSlotsForDay(
                $provider,
                $service,
                $currentDate,
                $bookedSlots
            );
            
            $slots = array_merge($slots, $dailySlots);
            
            // Move to next day
            $currentDate->modify('+1 day');
        }
        
        return $slots;
    }

    /**
     * Generates slots for a single day.
     * 
     * @param array<string, bool> $bookedSlots Map of booked datetime strings
     * @return array<string> Available slots for this day
     */
    private function generateSlotsForDay(
        Provider $provider,
        Service $service,
        \DateTime $date,
        array $bookedSlots
    ): array {
        $slots = [];
        
        // Check if provider works on this day
        if (!$this->workingHoursValidator->isWorkingDay($provider, $date)) {
            return $slots;
        }
        
        // Get working hours for this day
        $hours = $this->workingHoursValidator->getWorkingHoursForDay($provider, $date);
        if ($hours === null) {
            return $slots;
        }
        
        // Create datetime objects for working hours
        $slotTime = \DateTime::createFromFormat('Y-m-d H:i', 
            $date->format('Y-m-d') . ' ' . $hours['start']);
        $dayEndTime = \DateTime::createFromFormat('Y-m-d H:i', 
            $date->format('Y-m-d') . ' ' . $hours['end']);
        
        // Generate slots at regular intervals
        while ($slotTime < $dayEndTime) {
            // Check if service can be completed before closing time
            if ($this->workingHoursValidator->canServiceFitInWorkingHours($provider, $service, $slotTime)) {
                $slotString = $slotTime->format('Y-m-d H:i:s');
                
                // Only add if not already booked
                if (!isset($bookedSlots[$slotString])) {
                    $slots[] = $slotString;
                }
            }
            
            // Move to next slot interval (use service duration)
            $slotTime->modify('+' . $service->getDuration() . ' minutes');
        }
        
        return $slots;
    }

    /**
     * Gets a map of booked slots for a provider in a date range.
     * 
     * @return array<string, bool> Map where keys are datetime strings and values are true
     */
    private function getBookedSlots(
        Provider $provider,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        // Get all confirmed bookings for this provider in the date range
        $existingBookings = $this->bookingRepository->createQueryBuilder('b')
            ->where('b.provider = :provider')
            ->andWhere('b.datetime >= :startDate')
            ->andWhere('b.datetime <= :endDate')
            ->andWhere('b.status = :status')
            ->setParameter('provider', $provider)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('status', 'confirmed')
            ->getQuery()
            ->getResult();
        
        // Create a map for quick lookup
        $bookedSlots = [];
        foreach ($existingBookings as $booking) {
            $bookedSlots[$booking->getDatetime()->format('Y-m-d H:i:s')] = true;
        }
        
        return $bookedSlots;
    }

    /**
     * Checks if a specific slot is available.
     */
    public function isSlotAvailable(
        Provider $provider,
        \DateTimeInterface $datetime
    ): bool {
        // Check if there's a confirmed booking for this slot
        $existingBooking = $this->bookingRepository->createQueryBuilder('b')
            ->where('b.provider = :provider')
            ->andWhere('b.datetime = :datetime')
            ->andWhere('b.status = :status')
            ->setParameter('provider', $provider)
            ->setParameter('datetime', $datetime)
            ->setParameter('status', 'confirmed')
            ->getQuery()
            ->getOneOrNullResult();
        
        return $existingBooking === null;
    }
}
