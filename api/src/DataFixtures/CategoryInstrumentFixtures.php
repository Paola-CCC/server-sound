<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Instrument;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class CategoryInstrumentFixtures extends Fixture  implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $categories = ['Group Instruments', 'Theory', 'Sound recording', 'Music Ensembles', 'Gaming Music', 'Soloist Instruments'];  // add more if you want
        forEach($categories as $category) {
            $categoryInstance = new Category();
            $categoryInstance->setName($category);
            $manager->persist($categoryInstance);
        }   

           
        $instruments = ['Clarinette','Flûte','Piano','Violoncelle','Violon','Alto','Trombone','Hautbois','Orchestre','Voix'];
        forEach($instruments as $instrument) {
            $instrumentInstance = new Instrument();
            $instrumentInstance->setName($instrument);
            $instrumentInstance->setLevel([mt_rand(1, 10)]);
            $manager->persist($instrumentInstance);
        }   

        $manager->flush();
    }

    public function getOrder()
    {
        return 1; // Set the desired order number here
    }
    
}
