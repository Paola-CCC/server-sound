<?php

namespace App\DataFixtures;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ConversationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $users = $manager->getRepository(User::class)->findAll();

        for ($i = 0; $i < 20; $i++) {
            $userOne = $users[array_rand($users)];
            $userTwo =  $users[array_rand($users)];
            $conversation = new Conversation();
            $conversation->setUserOne($userOne);
            $conversation->setUserTwo($userTwo);
            $conversation->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($conversation);
        }

        $manager->flush();
    } 


    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    } 
}
