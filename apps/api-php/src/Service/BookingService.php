<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class BookingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WorkingHoursValidator $workingHoursValidator,
        private SlotGeneratorService $slotGeneratorService
    ) {
    }

    /**
     * Creates a new booking.
     * 
     * @throws \InvalidArgumentException If validation fails
     * @throws UniqueConstraintViolationException If slot is already booked (race condition)
     */
    public function createBooking(
        User $user,
        Provider $provider,
        Service $service,
        \DateTimeInterface $datetime
    ): Booking {
        // Validate working hours
        $validationError = $this->workingHoursValidator->getValidationError(
            $provider,
            $service,
            $datetime
        );
        
        if ($validationError !== null) {
            throw new \InvalidArgumentException($validationError);
        }
        
        // Check if slot is available
        if (!$this->slotGeneratorService->isSlotAvailable($provider, $datetime)) {
            throw new \InvalidArgumentException('Slot already booked');
        }
        
        // Create booking
        $booking = new Booking();
        $booking->setUser($user);
        $booking->setProvider($provider);
        $booking->setService($service);
        $booking->setDatetime($datetime);
        $booking->setStatus('confirmed');
        
        // Persist to database
        try {
            $this->entityManager->persist($booking);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            // Handle race condition where slot was booked between check and save
            throw new UniqueConstraintViolationException(
                'Slot already booked (race condition detected)',
                $e
            );
        }
        
        return $booking;
    }

    /**
     * Cancels a booking (soft delete).
     * 
     * @throws \InvalidArgumentException If booking not found or invalid
     */
    public function cancelBooking(Booking $booking): void
    {
        if ($booking->getStatus() === 'cancelled') {
            throw new \InvalidArgumentException('Booking is already cancelled');
        }
        
        $booking->setStatus('cancelled');
        $this->entityManager->flush();
    }

    /**
     * Hard deletes a booking (permanent removal from database).
     * 
     * @throws \InvalidArgumentException If booking cannot be deleted
     */
    public function hardDeleteBooking(Booking $booking): void
    {
        if ($booking->getStatus() !== 'cancelled') {
            throw new \InvalidArgumentException('Only cancelled bookings can be deleted');
        }
        
        // Use native SQL to truly delete the record
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement(
            'DELETE FROM booking WHERE id = :id',
            ['id' => $booking->getId()]
        );
    }

    /**
     * Checks if a user can cancel a booking.
     */
    public function canUserCancelBooking(Booking $booking, User $user, bool $isAdmin = false): bool
    {
        // Admin can cancel any booking
        if ($isAdmin) {
            return true;
        }
        
        // User can only cancel their own bookings
        return $booking->getUser() === $user;
    }

    /**
     * Gets all bookings for a user (excluding soft-deleted).
     * 
     * @return Booking[]
     */
    public function getUserBookings(User $user, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        return $this->entityManager->createQueryBuilder()
            ->select('b')
            ->from(Booking::class, 'b')
            ->where('b.user = :user')
            ->andWhere('b.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('b.datetime', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets total count of user bookings (for pagination).
     */
    public function getUserBookingsCount(User $user): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(b.id)')
            ->from(Booking::class, 'b')
            ->where('b.user = :user')
            ->andWhere('b.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Gets all bookings (admin function).
     * 
     * @return Booking[]
     */
    public function getAllBookings(int $page = 1, int $limit = 50): array
    {
        $offset = ($page - 1) * $limit;
        
        return $this->entityManager->createQueryBuilder()
            ->select('b')
            ->from(Booking::class, 'b')
            ->orderBy('b.datetime', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets total count of all bookings (for pagination).
     */
    public function getAllBookingsCount(): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(b.id)')
            ->from(Booking::class, 'b')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Validates booking data before creation.
     * 
     * @return array<string> Array of validation error messages (empty if valid)
     */
    public function validateBookingData(
        ?Provider $provider,
        ?Service $service,
        string $datetimeString
    ): array {
        $errors = [];
        
        // Validate entities exist
        if ($provider === null) {
            $errors[] = 'Invalid provider';
        }
        
        if ($service === null) {
            $errors[] = 'Invalid service';
        }
        
        // Parse datetime
        try {
            $datetime = new \DateTime($datetimeString);
        } catch (\Exception $e) {
            $errors[] = 'Invalid datetime format. Expected format: YYYY-MM-DD HH:MM:SS';
            return $errors; // Can't continue validation without valid datetime
        }
        
        // Validate working hours if we have provider and service
        if ($provider !== null && $service !== null) {
            $validationError = $this->workingHoursValidator->getValidationError(
                $provider,
                $service,
                $datetime
            );
            
            if ($validationError !== null) {
                $errors[] = $validationError;
            }
        }
        
        return $errors;
    }
}
