<?php

namespace App\DataFixtures;

use App\Entity\Conversation;
use App\Entity\Message;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MessageFixtures extends Fixture implements DependentFixtureInterface
{

    private $faker;


    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {

        $allConversation = $manager->getRepository(Conversation::class)->findAll();

            foreach ($allConversation as $conversation) {

                $conversation = $manager->getRepository(Conversation::class)->find($conversation->getId());

                $userOne = $conversation->getUserOne();
                $userTwo = $conversation->getUserTwo();
                $users = [$userOne,$userTwo];
                $key = array_rand($users);
                $message = new Message();
                $message->setAuthor($users[$key]);
                $message->setContentText($this->faker->realText());
                $message->setSentDate(new \DateTimeImmutable());    
                $message->setConversation($conversation);
                $manager->persist($message);

            }

            $manager->flush();

        }


    public function getDependencies()
    {
        return [
            ConversationFixtures::class,
        ];
    }



}
