<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Progression;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgressionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $courses = $manager->getRepository(Course::class)->findAll();

        foreach ($courses as $course) {                
            $courseObj = $manager->getRepository(Course::class)->find($course->getId());
            if( $courseObj) {
                foreach ($course->getUsers() as $user) {
                    $usersCourse = $manager->getRepository(User::class)->find($user);
                    $progression = new Progression();
                    $progression->setUser($usersCourse);
                    $progression->setCourse($courseObj);
                    $progression->setVideoTimestamp('0:00');
                    $progression->setStatus('NOT_STARTED');
                    $progression->setQuizzStatus('HIDDEN');
                    $progression->setCreatedAt(new \DateTimeImmutable());
                    $progression->setUpdateAt(new \DateTimeImmutable());
                    $progression->setPercentageWatched(0);
                    $manager->persist($progression);
                }
            }
        }
        $manager->flush();

    }


    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CourseFixtures::class
        ];
    }
}
