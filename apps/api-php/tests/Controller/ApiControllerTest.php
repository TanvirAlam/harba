<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testProfileReturnsUserData(): void
    {
        $client = static::createClient();

        $user = new User();
        $user->setEmail('profile' . time() . '@example.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);

        // Persist the user to get an ID
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $client->request('GET', '/api/profile');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringStartsWith('profile', $responseData['email']);
        $this->assertStringEndsWith('@example.com', $responseData['email']);
        $this->assertContains('ROLE_ADMIN', $responseData['roles']);
        $this->assertContains('ROLE_USER', $responseData['roles']);
    }

    public function testProfileWithoutAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/profile');

        $this->assertResponseStatusCodeSame(401); // Assuming security redirects or denies
    }
}