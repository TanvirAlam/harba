<?php

namespace App\Service;

use App\Entity\Provider;
use App\Entity\Service;

class WorkingHoursValidator
{
    /**
     * Validates if a provider works on a given day of the week.
     */
    public function isWorkingDay(Provider $provider, \DateTimeInterface $datetime): bool
    {
        $dayOfWeek = strtolower($datetime->format('l')); // monday, tuesday, etc.
        $workingHours = $provider->getWorkingHours();
        
        return isset($workingHours[$dayOfWeek]) && !empty($workingHours[$dayOfWeek]);
    }

    /**
     * Gets the working hours for a specific day.
     * 
     * @return array{start: string, end: string}|null Returns ['start' => '09:00', 'end' => '17:00'] or null
     */
    public function getWorkingHoursForDay(Provider $provider, \DateTimeInterface $datetime): ?array
    {
        $dayOfWeek = strtolower($datetime->format('l'));
        $workingHours = $provider->getWorkingHours();
        
        if (!isset($workingHours[$dayOfWeek]) || empty($workingHours[$dayOfWeek])) {
            return null;
        }
        
        $hours = $workingHours[$dayOfWeek];
        
        // Parse working hours (e.g., "09:00-17:00")
        if (preg_match('/^(\d{2}:\d{2})-(\d{2}:\d{2})$/', $hours, $matches)) {
            return [
                'start' => $matches[1],
                'end' => $matches[2],
            ];
        }
        
        return null;
    }

    /**
     * Validates if a booking time falls within provider working hours.
     */
    public function isWithinWorkingHours(
        Provider $provider,
        \DateTimeInterface $datetime
    ): bool {
        $hours = $this->getWorkingHoursForDay($provider, $datetime);
        
        if ($hours === null) {
            return false;
        }
        
        $startTime = \DateTime::createFromFormat('Y-m-d H:i', 
            $datetime->format('Y-m-d') . ' ' . $hours['start']);
        $endTime = \DateTime::createFromFormat('Y-m-d H:i', 
            $datetime->format('Y-m-d') . ' ' . $hours['end']);
        
        return $datetime >= $startTime && $datetime < $endTime;
    }

    /**
     * Validates if a service can be completed before provider closes.
     */
    public function canServiceFitInWorkingHours(
        Provider $provider,
        Service $service,
        \DateTimeInterface $bookingStartTime
    ): bool {
        $hours = $this->getWorkingHoursForDay($provider, $bookingStartTime);
        
        if ($hours === null) {
            return false;
        }
        
        $endTime = \DateTime::createFromFormat('Y-m-d H:i', 
            $bookingStartTime->format('Y-m-d') . ' ' . $hours['end']);
        
        $serviceEndTime = clone $bookingStartTime;
        $serviceEndTime->modify("+{$service->getDuration()} minutes");
        
        return $serviceEndTime <= $endTime;
    }

    /**
     * Gets error message for why a booking time is invalid.
     */
    public function getValidationError(
        Provider $provider,
        Service $service,
        \DateTimeInterface $datetime
    ): ?string {
        $dayOfWeek = strtolower($datetime->format('l'));
        
        // Check if provider works on this day
        if (!$this->isWorkingDay($provider, $datetime)) {
            return "Provider does not work on {$dayOfWeek}";
        }
        
        $hours = $this->getWorkingHoursForDay($provider, $datetime);
        
        if ($hours === null) {
            return "Invalid working hours format for provider";
        }
        
        // Check if booking time is within working hours
        if (!$this->isWithinWorkingHours($provider, $datetime)) {
            return "Booking time is outside provider working hours ({$hours['start']}-{$hours['end']})";
        }
        
        // Check if service duration fits within working hours
        if (!$this->canServiceFitInWorkingHours($provider, $service, $datetime)) {
            return "Service duration extends beyond provider closing time";
        }
        
        return null;
    }
}
