<?php
// src/DataFixtures/FakeDataFactory.php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Note;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FakeDataFactory extends Fixture
{
    private $userRepository;
    private $userPasswordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $usersArray = [];
        $i = 0;
        while ($i < 10) {
            $user = new User;
            $user->setEmail("dev@dev$i.com");
            $user->setName("dev$i");
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    "123456"
                )
            );
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setProfilePictureUrl('https://cdn-icons-png.flaticon.com/128/7915/7915354.png');
            $usersArray[] = $user;
            $manager->persist($user);
            $i++;
        }

        foreach ($usersArray as $user) {
            // dump($user);
            $j = 0;
            while ($j < 11) {
                $note = new Note();
                $note->setTitle($faker->sentence);
                $note->setContent($faker->paragraph);
                $note->setCreatedAt(new \DateTimeImmutable());
                $note->setUpdatedAt(new \DateTimeImmutable());
                $note->setOwner($user);

                $manager->persist($note);
                $j++;
            }
        }

        $manager->flush();
    }
}
