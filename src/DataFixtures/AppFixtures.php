<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $clientPasswordHaser;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->clientPasswordHaser = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        /** Creation products */
        for ($i = 1; $i <= 10; $i++) {
            $product = new Product();
            $product->setName('product '.$i);
            $product->setPrice(mt_rand(10, 100));
            $product->setColor('color '.$i);
            $product->setDescription('description '.$i);
            $product->setWeight(mt_rand(10, 100));
            $product->setHeight(mt_rand(10, 100));
            $product->setWidth(mt_rand(10, 100));
            $product->setCapacity('capacity '.$i);
            $product->setProcessor('processor '.$i);
            $product->setThickness(mt_rand(10, 100));
            $product->setScreen('screen '.$i);

            $manager->persist($product);
        }

        /** creation clients */
        $clientList = [];

        /* Creation client "normal" */
        $client = new Client();
        $client->setEmail("user@bilemoapi.com");
        $client->setName('client');
        $client->setRoles(["ROLE_USER"]);
        $client->setPassword($this->clientPasswordHaser->hashPassword($client, "password"));
        $manager->persist($client);

        $clientList[] = $client;
        
        /* Création d'un client admin */
        $clientAdmin = new Client();
        $clientAdmin->setEmail("admin@bilemoapi.com");
        $clientAdmin->setName('clientAdmin');
        $clientAdmin->setRoles(["ROLE_ADMIN"]);
        $clientAdmin->setPassword($this->clientPasswordHaser->hashPassword($clientAdmin, "password"));
        $manager->persist($clientAdmin);

        $clientList[] = $clientAdmin;

        /** creation users */
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail('emailuser'.$i);
            $user->setFirstname('firstname '.$i);
            $user->setLastname('lastname '.$i);
            $user->setClient($clientList[array_rand($clientList)]);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
