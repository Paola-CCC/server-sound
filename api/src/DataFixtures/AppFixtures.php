<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Category;
use App\Entity\Composer;
use App\Entity\Instrument;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture

{
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create();

        $composers = ['Ludwig Van Beethoven', 'Wolgang A. Mozart', 'Agustin B. Mangoré', 'Johan Sebastian Bach', 'Franz Liszt', 'Antonio Vivaldi', 'Francisco Tarrega', 'Giuseppe Verdi', 'Claude Debussy', 'Hans Zimmerman'];
        $instrumentsInstances = $manager->getRepository(Instrument::class)->findAll();

        forEach($composers as $composer) {
            $composerInstance = new Composer();
            $composerInstance->setFullName($composer);
            $composerInstance->setBiography($faker->paragraph());
            $composerInstance->addInstrument($instrumentsInstances[array_rand($instrumentsInstances)]);
            $manager->persist($composerInstance);
        }   

        for ( $i = 0; $i < 10; $i++)
        {
            $profNames = explode(" ", $faker->name());
            $profInsert = new User();
            $profInsert->setFirstName($profNames[0]);
            $profInsert->setLastName($profNames[1]);
            $profInsert->setEmail($faker->email());
            $profInsert->setPhoto($faker->imageUrl(640, 480, 'ROLE_PROFESSOR', true));
            $profInsert->setPassword(password_hash('12345678', PASSWORD_DEFAULT));
            $profInsert->setRoles(['ROLE_PROFESSOR']);
            $profInsert->addInstrument($instrumentsInstances[array_rand($instrumentsInstances)]);
            $manager->persist($profInsert);
        }
        
        $manager->flush();
    }
}
    

