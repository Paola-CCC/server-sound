<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Comment;
use App\DataFixtures\UserFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $courses = $manager->getRepository(Course::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();


        for ($i = 0; $i < 10; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->paragraph());
            $comment->setCourse($courses[array_rand($courses)]);
            $comment->setUser($users[array_rand($users)]);
            $manager->persist($comment);
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
