<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('user1');
        $user->setLastName('user1_lastname');
        $user->setAge(100);
        $user->setEmail('user1_email@gmail.com');
        $user->setPhone(123456789);
        
        $manager->persist($user);
        $manager->flush();

        return $user;
      
    }
}