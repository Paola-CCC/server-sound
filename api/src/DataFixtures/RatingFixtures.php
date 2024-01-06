<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\Rating;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class RatingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $courses = $manager->getRepository(Course::class)->findAll();

        forEach($users as $user) {
            $rating = new Rating();
            $rating->setValue(array_rand(range(1, 10)));
            $rating->setUser($users[array_rand($users)]);
            $rating->setCourse($courses[array_rand($courses)]);
    
            $manager->persist($rating);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }   
}
