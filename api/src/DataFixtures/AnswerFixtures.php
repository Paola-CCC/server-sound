<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Forum;
use App\Entity\Answer;
use App\DataFixtures\ForumFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AnswerFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $users = $manager->getRepository(User::class)->findAll();
        $forums = $manager->getRepository(Forum::class)->findAll();


        $faker = Factory::create();

        for ( $i = 0; $i < 10; $i++)
        {
            $answer = new Answer();
            $answer->setForum($forums[array_rand($forums)]);
            $answer->setAuthor($users[array_rand($users)]);
            $answer->setContent($faker->paragraph());
            $manager->persist($answer);
        }
  
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ForumFixtures::class,
        ];
    }
}
