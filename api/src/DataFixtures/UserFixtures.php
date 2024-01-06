<?php
// fixtures à faire : instruments, category, composer

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Comment;
use App\Entity\Category;
use App\Entity\Composer;
use App\Entity\Instrument;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\CategoryInstrumentFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class UserFixtures extends Fixture implements DependentFixtureInterface
{

    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {

            $instruments = $manager->getRepository(Instrument::class)->findAll();
            $courses = $manager->getRepository(Course::class)->findAll();

                /**USER */ 
                $user = new User();
                $user->setFirstName('John-David')
                    ->setLastName('DOE')
                    ->setEmail('admin@saline-dev.com')
                    ->setUsername('Joe-DD')
                    ->setPassword(password_hash('87654321', PASSWORD_DEFAULT))
                    ->setPhoto($this->faker->imageUrl(640, 480, 'people', true))
                    ->setRoles(['ROLE_SUPER_ADMIN']);
                    $manager->persist($user);

                for ($i = 0; $i < 8; $i++) {
                    $firstnameAdmin = $this->faker->firstName;
                    $lastNameAdmin = $this->faker->lastName;
                    $userAdmin = new User();
                    $userAdmin->setFirstName($firstnameAdmin)
                    ->setLastName($lastNameAdmin)
                    ->setEmail( $firstnameAdmin.'.'.$lastNameAdmin.'@saline-dev.com')
                    ->setUsername($firstnameAdmin. '@' . $lastNameAdmin)
                    ->setPassword(password_hash('87654321', PASSWORD_DEFAULT))
                    ->setPhoto($this->faker->imageUrl(640, 480, 'people', true))
                    ->setRoles(['ROLE_SUPER_ADMIN']);
                    $manager->persist($userAdmin);

                }
                    
                for ($i = 0; $i < 10; $i++) {
                    $profNames = explode(" ", $this->faker->name());
                    $userInsert = new User();
                    $userInsert->setFirstName($this->faker->firstName);
                    $userInsert->setLastName($this->faker->lastName);
                    $userInsert->setEmail($this->faker->email);
                    $userInsert->setUsername($this->faker->firstName. '@' .$this->faker->lastName);
                    $userInsert->setPassword(password_hash('12345678', PASSWORD_DEFAULT));
                    $userInsert->addInstrument($instruments[array_rand($instruments)]);
                    $userInsert->setPhoto($this->faker->imageUrl(640, 480, 'people', true));
                    $randomRole = rand(1, 10);

                    for( $j = 0; $j < 8; $j++) {
                        $userInsert->addCourse(
                            $courses[mt_rand(0, count($courses) - 1)]
                        );
                    }

                    if ($randomRole === 5 || $randomRole === 10) {
                        $userInsert->setRoles(['ROLE_ADMIN']);
                    } elseif ($randomRole === 7 || $randomRole === 4 || $randomRole === 8 || $randomRole === 2) {
                        $userInsert->setRoles(['ROLE_PROFESSOR']);
                        $userInsert->setBiography($this->faker->paragraph());
                    } else {
                        $userInsert->setRoles(['ROLE_USER']);
                    }
                    $manager->persist($userInsert);
                }
                
                $manager->flush();

        }

        public function getDependencies()
        {
            return [
                CourseFixtures::class,
            ];
        }    
}