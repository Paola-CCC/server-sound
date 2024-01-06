<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SubscriptionFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {

        $descriptionTWO = "Vidéos disponibles en HD sur tous vos appareils.\n\nAccès illimité à toutes nos masterclasses.\n\nPartitions annotées par nos professeurs en ligne";
        $subscriptionTwo = new Subscription();
        $subscriptionTwo->setName('Standard');
        $subscriptionTwo->setDescription($descriptionTWO);
        $subscriptionTwo->setPrice(19.99);
        $manager->persist($subscriptionTwo);

        $descriptionOne = "Vidéos disponibles en 4K Ultra HD sur tous vos appareils.\n\nAccès illimité à toutes nos masterclasses.\n\nPartitions annotées par nos professeurs et prêtes à télécharger";
        $subscriptionOne = new Subscription();
        $subscriptionOne->setName('Premium');
        $subscriptionOne->setDescription($descriptionOne);
        $subscriptionOne->setPrice(39.99);
        $manager->persist($subscriptionOne);
        $manager->flush();
    }
}
