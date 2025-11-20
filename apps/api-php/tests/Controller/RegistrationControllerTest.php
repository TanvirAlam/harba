<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterWithValidData(): void
    {
        $client = static::createClient();

        $data = [
            'email' => 'register1' . time() . '@example.com',
            'password' => 'password123',
            'roles' => ['ROLE_ADMIN']
        ];

        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('User registered successfully', $responseData['message']);
    }

    public function testRegisterWithMissingEmail(): void
    {
        $client = static::createClient();

        $data = [
            'password' => 'password123'
        ];

        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid data', $responseData['error']);
    }

    public function testRegisterWithMissingPassword(): void
    {
        $client = static::createClient();

        $data = [
            'email' => 'register3' . time() . '@example.com'
        ];

        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid data', $responseData['error']);
    }

    public function testRegisterWithDefaultRoles(): void
    {
        $client = static::createClient();

        $data = [
            'email' => 'register4' . time() . '@example.com',
            'password' => 'password123'
        ];

        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(201);
    }
}