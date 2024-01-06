<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Forum;
use App\Entity\Category;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\CommentFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ForumFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $categories = $manager->getRepository(Category::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        $faker = Factory::create();


        for ( $i = 0; $i < 30; $i++)
        {
            $forumInsert = new Forum();
            $forumInsert->setSubject($faker->sentence());
            $forumInsert->setDescription($faker->paragraph());
            $forumInsert->setAuthor($users[array_rand($users)]);
            $forumInsert->addCategory($categories[array_rand($categories)]);
            $manager->persist($forumInsert);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CommentFixtures::class,
        ];
    }
}
