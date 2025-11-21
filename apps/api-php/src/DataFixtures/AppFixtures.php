<?php

namespace App\DataFixtures;

use App\Entity\Service;
use App\Entity\Provider;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create services
        $haircut = new Service();
        $haircut->setName('Haircut');
        $haircut->setDuration(30);
        $manager->persist($haircut);

        $massage = new Service();
        $massage->setName('Massage');
        $massage->setDuration(60);
        $manager->persist($massage);

        $facial = new Service();
        $facial->setName('Facial');
        $facial->setDuration(45);
        $manager->persist($facial);

        // Create providers
        $john = new Provider();
        $john->setName('John Doe');
        $john->setWorkingHours([
            'monday' => '09:00-17:00',
            'tuesday' => '09:00-17:00',
            'wednesday' => '09:00-17:00',
            'thursday' => '09:00-17:00',
            'friday' => '09:00-17:00',
            'saturday' => '10:00-16:00',
            'sunday' => 'closed'
        ]);
        $manager->persist($john);

        $jane = new Provider();
        $jane->setName('Jane Smith');
        $jane->setWorkingHours([
            'monday' => '10:00-18:00',
            'tuesday' => '10:00-18:00',
            'wednesday' => '10:00-18:00',
            'thursday' => '10:00-18:00',
            'friday' => '10:00-18:00',
            'saturday' => '11:00-17:00',
            'sunday' => 'closed'
        ]);
        $manager->persist($jane);

        // Create users
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        $manager->flush();
    }
}
