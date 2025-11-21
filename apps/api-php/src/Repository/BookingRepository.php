<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Provider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findBookingsForProviderAndDate(Provider $provider, \DateTimeInterface $date): array
    {
        $start = new \DateTime($date->format('Y-m-d') . ' 00:00:00');
        $end = new \DateTime($date->format('Y-m-d') . ' 23:59:59');

        return $this->createQueryBuilder('b')
            ->andWhere('b.provider = :provider')
            ->andWhere('b.datetime BETWEEN :start AND :end')
            ->setParameter('provider', $provider)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function findBookingsForProviderBetweenDates(Provider $provider, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.provider = :provider')
            ->andWhere('b.datetime BETWEEN :start AND :end')
            ->setParameter('provider', $provider)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getResult();
    }
}