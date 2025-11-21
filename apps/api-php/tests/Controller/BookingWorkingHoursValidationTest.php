<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BookingWorkingHoursValidationTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private ?string $token = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Clean up test data
        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Provider')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Service')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        
        // Create and authenticate test user
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // Login to get token
        $this->client->request('POST', '/api/login_check', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['username' => 'test@example.com', 'password' => 'password123'])
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->token = $response['token'];
    }

    protected function tearDown(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Provider')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Service')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();
        parent::tearDown();
    }

    public function testBookingOutsideWorkingHoursIsRejected(): void
    {
        // Create provider who works Mon-Fri 09:00-17:00
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours([
            'Monday' => '09:00-17:00',
            'Tuesday' => '09:00-17:00',
            'Wednesday' => '09:00-17:00',
            'Thursday' => '09:00-17:00',
            'Friday' => '09:00-17:00',
        ]);
        $this->entityManager->persist($provider);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(30);
        $this->entityManager->persist($service);

        $this->entityManager->flush();

        // Try to book at 23:00 (outside working hours)
        $futureDate = (new \DateTime('next Monday'))->setTime(23, 0, 0);
        
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode([
                'provider_id' => $provider->getId(),
                'service_id' => $service->getId(),
                'datetime' => $futureDate->format('Y-m-d H:i:s'),
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('outside provider working hours', $response['error']);
        
        // Verify booking was NOT created
        $bookings = $this->entityManager->getRepository(Booking::class)->findAll();
        $this->assertCount(0, $bookings);
    }

    public function testBookingBeforeWorkingHoursIsRejected(): void
    {
        // Create provider who works 09:00-17:00
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours([
            'Monday' => '09:00-17:00',
        ]);
        $this->entityManager->persist($provider);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(30);
        $this->entityManager->persist($service);

        $this->entityManager->flush();

        // Try to book at 08:00 (before opening)
        $futureDate = (new \DateTime('next Monday'))->setTime(8, 0, 0);
        
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode([
                'provider_id' => $provider->getId(),
                'service_id' => $service->getId(),
                'datetime' => $futureDate->format('Y-m-d H:i:s'),
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('outside provider working hours', $response['error']);
    }

    public function testBookingOnNonWorkingDayIsRejected(): void
    {
        // Create provider who only works Monday
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours([
            'Monday' => '09:00-17:00',
        ]);
        $this->entityManager->persist($provider);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(30);
        $this->entityManager->persist($service);

        $this->entityManager->flush();

        // Try to book on Tuesday (provider doesn't work)
        $futureDate = (new \DateTime('next Tuesday'))->setTime(10, 0, 0);
        
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode([
                'provider_id' => $provider->getId(),
                'service_id' => $service->getId(),
                'datetime' => $futureDate->format('Y-m-d H:i:s'),
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('does not work on', $response['error']);
    }

    public function testServiceExtendingBeyondClosingTimeIsRejected(): void
    {
        // Create provider who works 09:00-17:00
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours([
            'Monday' => '09:00-17:00',
        ]);
        $this->entityManager->persist($provider);

        // Service takes 60 minutes
        $service = new Service();
        $service->setName('Long Service');
        $service->setDuration(60);
        $this->entityManager->persist($service);

        $this->entityManager->flush();

        // Try to book at 16:30 (service ends at 17:30, after closing at 17:00)
        $futureDate = (new \DateTime('next Monday'))->setTime(16, 30, 0);
        
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode([
                'provider_id' => $provider->getId(),
                'service_id' => $service->getId(),
                'datetime' => $futureDate->format('Y-m-d H:i:s'),
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('extends beyond provider closing time', $response['error']);
    }

    public function testServiceThatFitsExactlyAtClosingTimeIsAccepted(): void
    {
        // Create provider who works 09:00-17:00
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours([
            'Monday' => '09:00-17:00',
        ]);
        $this->entityManager->persist($provider);

        // Service takes 60 minutes
        $service = new Service();
        $service->setName('Service');
        $service->setDuration(60);
        $this->entityManager->persist($service);

        $this->entityManager->flush();

        // Book at 16:00 (service ends exactly at 17:00)
        $futureDate = (new \DateTime('next Monday'))->setTime(16, 0, 0);
        
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode([
                'provider_id' => $provider->getId(),
                'service_id' => $service->getId(),
                'datetime' => $futureDate->format('Y-m-d H:i:s'),
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        
        // Verify booking was created
        $bookings = $this->entityManager->getRepository(Booking::class)->findAll();
        $this->assertCount(1, $bookings);
    }

    public function testValidBookingWithinWorkingHoursIsAccepted(): void
    {
        // Create provider who works 09:00-17:00
        $provider = new Provider();
        $provider->setName('Test Provider');
        $provider->setWorkingHours([
            'Monday' => '09:00-17:00',
        ]);
        $this->entityManager->persist($provider);

        $service = new Service();
        $service->setName('Test Service');
        $service->setDuration(30);
        $this->entityManager->persist($service);

        $this->entityManager->flush();

        // Book at 10:00 (valid time)
        $futureDate = (new \DateTime('next Monday'))->setTime(10, 0, 0);
        
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode([
                'provider_id' => $provider->getId(),
                'service_id' => $service->getId(),
                'datetime' => $futureDate->format('Y-m-d H:i:s'),
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        
        // Verify booking was created
        $bookings = $this->entityManager->getRepository(Booking::class)->findAll();
        $this->assertCount(1, $bookings);
        $this->assertEquals($futureDate->format('Y-m-d H:i:s'), 
                          $bookings[0]->getDatetime()->format('Y-m-d H:i:s'));
    }
}
