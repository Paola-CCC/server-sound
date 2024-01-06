<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Quizz;
use App\Entity\Question;
use App\Entity\Suggest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class QuizzFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {

        $jsonFilePath = '/var/www/html' . '/json-files/quizz.json';
        $jsonString = file_get_contents($jsonFilePath);
        $jsonData = json_decode($jsonString, true);

        // if (file_exists($jsonFilePath)) {
        //     $jsonString = file_get_contents($jsonFilePath);
        //     $jsonData = json_decode($jsonString, true);
        // } 

        $courses = $manager->getRepository(Course::class)->findAll();

        foreach ($courses as $course) {

            $courseObj = $manager->getRepository(Course::class)->find($course->getId());

            if ($courseObj) {
                $quizz = new Quizz();
                $quizz->setCourse($courseObj);
                $quizz->setTitle($jsonData['title']);
                foreach ($jsonData['questions'] as $questionData) {
                    $quizzQuestion = new Question();
                    $quizzQuestion->setContent($questionData['content']);
                    $quizz->addQuestion($quizzQuestion);
                    foreach ($questionData['suggests'] as $suggestData) {
                        $suggest = new Suggest();
                        $suggest->setContent($suggestData['content']);
                        $suggest->setResponseExpected($suggestData['response_expected']);
                        $quizzQuestion->addSuggest($suggest);
                        $manager->persist($suggest);
                    }
                    $manager->persist($quizzQuestion);
                }
                $manager->persist($quizz);

            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CourseFixtures::class,
            // Suggest::class,
            // Quizz::class
        ];
    }
}
