<?php

namespace App\Service;

use App\Entity\Provider;
use App\Entity\Service;

class WorkingHoursValidator
{
    private const VALID_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    /**
     * Validates the format and logic of a time string (HH:MM).
     * 
     * @return array{valid: bool, error: ?string}
     */
    public function validateTimeFormat(string $time): array
    {
        // Check format
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return ['valid' => false, 'error' => 'Time must be in HH:MM format'];
        }
        
        [$hours, $minutes] = explode(':', $time);
        $hours = (int) $hours;
        $minutes = (int) $minutes;
        
        // Validate ranges
        if ($hours < 0 || $hours > 23) {
            return ['valid' => false, 'error' => 'Hours must be between 00 and 23'];
        }
        
        if ($minutes < 0 || $minutes > 59) {
            return ['valid' => false, 'error' => 'Minutes must be between 00 and 59'];
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    /**
     * Validates working hours string format and logic.
     * 
     * @return array{valid: bool, error: ?string}
     */
    public function validateWorkingHoursString(string $hoursString): array
    {
        // Check format: HH:MM-HH:MM
        if (!preg_match('/^(\d{2}:\d{2})-(\d{2}:\d{2})$/', $hoursString, $matches)) {
            return ['valid' => false, 'error' => 'Working hours must be in format HH:MM-HH:MM'];
        }
        
        $startTime = $matches[1];
        $endTime = $matches[2];
        
        // Validate start time format
        $startValidation = $this->validateTimeFormat($startTime);
        if (!$startValidation['valid']) {
            return ['valid' => false, 'error' => 'Start time: ' . $startValidation['error']];
        }
        
        // Validate end time format
        $endValidation = $this->validateTimeFormat($endTime);
        if (!$endValidation['valid']) {
            return ['valid' => false, 'error' => 'End time: ' . $endValidation['error']];
        }
        
        // Validate start < end
        if ($startTime >= $endTime) {
            return ['valid' => false, 'error' => 'Start time must be before end time'];
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    /**
     * Validates entire working hours array structure.
     * 
     * @return array<string> Array of validation errors (empty if valid)
     */
    public function validateWorkingHoursArray(array $workingHours): array
    {
        $errors = [];
        
        foreach ($workingHours as $day => $hours) {
            $dayLower = strtolower($day);
            
            // Validate day name
            if (!in_array($dayLower, self::VALID_DAYS, true)) {
                $errors[] = "Invalid day name: {$day}. Must be one of: " . implode(', ', self::VALID_DAYS);
                continue;
            }
            
            // Empty hours means day off - that's valid
            if (empty($hours)) {
                continue;
            }
            
            // Validate hours format
            $validation = $this->validateWorkingHoursString($hours);
            if (!$validation['valid']) {
                $errors[] = "{$day}: {$validation['error']}";
            }
        }
        
        return $errors;
    }
    
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
