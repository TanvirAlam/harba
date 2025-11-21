<?php

namespace App\Tests\Controller;

use App\Entity\Provider;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class JsonParsingErrorTest extends WebTestCase
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

    public function testMalformedJsonInBookingReturns400(): void
    {
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            'invalid{json'  // Malformed JSON
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('Invalid JSON', $response['error']);
    }

    public function testEmptyBodyInBookingReturns400(): void
    {
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            ''  // Empty body
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('Empty request body', $response['error']);
    }

    public function testMalformedJsonInLoginReturns400(): void
    {
        $this->client->request('POST', '/api/login_check', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            '{username: "test"'  // Malformed JSON (missing quotes)
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('Invalid JSON', $response['error']);
    }

    public function testMalformedJsonInRegisterReturns400(): void
    {
        $this->client->request('POST', '/api/register', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email": test@example.com}'  // Malformed JSON (unquoted value)
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('Invalid JSON', $response['error']);
    }

    public function testValidJsonWithMissingFieldsShowsClearError(): void
    {
        $this->client->request('POST', '/api/bookings', [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            ],
            json_encode(['provider_id' => 1])  // Missing service_id and datetime
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        // Should NOT say "Invalid JSON", should say fields are required
        $this->assertStringNotContainsString('Invalid JSON', $response['error']);
        $this->assertStringContainsString('required', $response['error']);
    }
}
