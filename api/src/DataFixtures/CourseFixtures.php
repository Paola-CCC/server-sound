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
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CourseFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $instruments = $manager->getRepository(Instrument::class)->findAll();
        $professors = $manager->getRepository(User::class)->findBy(['roles' => ['ROLE_PROFESSOR']]);
        $categories = $manager->getRepository(Category::class)->findAll();
        $composers = $manager->getRepository(Composer::class)->findAll();

        // COURSE
        $courses = [
            'Playing Baroque',
            'Jazz Harmony',
            'Afro-Caribbean Rhythms',
            'Theory & Solfege',
            'Orchestral Practice',
            'Counterpoint',
            'Ear Training',
            'Classical Composition',
            'Symphonic Analysis',
            'Piano Sonata Interpretation',
            'Opera History',
            'Chamber Music Ensemble',
            'Romantic Era Styles',
            'Conducting Techniques',
            'Music Theory in Practice',
            'Baroque Instrumental Performance'
        ];
        
    
        for ($i = 0; $i < 70 ; $i++){

            // Obtenir un indice aléatoire dans le tableau
            $courseInstance = new Course();
            $courseInstance->setTitle($courses[array_rand($courses)]);
            $courseInstance->setDescription($faker->paragraph());
            $courseInstance->setPrice($faker->randomNumber(5, false));
            $courseInstance->setLinkVideo($faker->url());
            $courseInstance->setProfessor($professors[array_rand($professors)]);
            $courseInstance->setInstrument($instruments[array_rand($instruments)]);
            $courseInstance->setPhoto($faker->imageUrl(640, 480, 'animals', true));
            $courseInstance->setPreview($faker->paragraph());
            $courseInstance->setRatingScore(mt_rand(0, 10));
            $courseInstance->setFiles([$faker->word(). '.pdf']);
            $courseInstance->addCategory($categories[array_rand($categories)]);
            $courseInstance->addComposer($composers[array_rand($composers)]);

            $manager->persist($courseInstance);
        }
                
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategoryInstrumentFixtures::class,
        ];
    }

}
