<?php

namespace App\Controller;

use App\Entity\Quizz;
use App\Entity\Question;
use App\Entity\Suggest;
use App\Entity\HistoQuizz;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CourseRepository;
use App\Repository\QuizzRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuizzController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/quizzes', name: 'quizz_list', methods: ['GET'])]
    public function getQuizzList(QuizzRepository $quizzRepository): JsonResponse
    {
        $quizzList = $quizzRepository->findAll();

        $response = $this->json($quizzList, 200, [], ['groups' => ['quizz','question', 'course_title']]);
        return $response;
    }

    #[Route('/quizzes/{quizzId}', name: 'quizz_by_id', methods: ['GET'])]
    public function getQuizzById(int $quizzId, QuizzRepository $quizzRepository): JsonResponse
    {
        $quizz = $quizzRepository->find($quizzId);

        if (!$quizz) {
            return new JsonResponse('Quizz not found', 404);
        }

        $response = $this->json($quizz, 200, [], ['groups' => ['quizz','question', 'course_title', 'course_quizz']]);
        
        return $response;
    }

    #[Route('/quizzes/course/{courseId}', name: 'quizz_course_by_id', methods: ['GET'])]
    public function getQuizzByCourseId(int $courseId, QuizzRepository $quizzRepository, CourseRepository $courseRepository): JsonResponse
    {
        $course = $courseRepository->find($courseId);

        if (!$course) {
            return new JsonResponse('Course not found', 404);
        }

        $quizzId = $quizzRepository->findOneBy(['course' => $courseId]);

        if (!$quizzId) {
            return new JsonResponse('Quizz not found', 404);
        }
        $data = [];
        $data[] = $quizzId;
        
        $response = $this->json($data, 200, [], ['groups' => ['quizz_id','quizz','question','course_title', 'course_quizz']]);
        
        return $response;
    }


    #[Route('/courses/{courseId}/new-quizz', name: 'new_quizz', methods: ['POST'])]
    public function newQuizz(int $courseId, Request $request, EntityManagerInterface $entityManager, CourseRepository $courseRepository): Response    
    
    {
        $course = $courseRepository->find($courseId);

        if (!$course) {
            return new JsonResponse(['message' => 'Course not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $quizz = new Quizz();

        $quizz->setCourse($course);
        $quizz->setTitle($data['title']);
        
        foreach ($data['questions'] as $questionData) {
            $quizzQuestion = new Question();
            $quizzQuestion->setContent($questionData['content']);
            $quizz->addQuestion($quizzQuestion);

            foreach ($questionData['suggests'] as $suggestData) {
                $suggest = new Suggest();
                $suggest->setContent($suggestData['content']);
                $suggest->setResponseExpected($suggestData['response_expected']);
                $quizzQuestion->addSuggest($suggest);

                $entityManager->persist($suggest);
            }

            $entityManager->persist($quizzQuestion);
        }

        $entityManager->persist($quizz);
        $entityManager->flush();

        return new Response('Quizz addeded successfully', Response::HTTP_OK);
    }

   #[Route('/quizzes/{quizzId}/add-response', name: 'add_response_quizz', methods: ['POST'])]
    public function addResponseQuiz(int $quizzId, Request $request, QuizzRepository $quizzRepository): JsonResponse
    {
        $quizz = $quizzRepository->find($quizzId);
    
        if (!$quizz) {
            return new JsonResponse(['message' => 'Quizz not found'], 404);
        }
    
        $data = json_decode($request->getContent(), true);
        $score = 0;
        $suggestAllAnswers = [];
        $userId = null;

        foreach ($data['answers'] as $answer) {
            $questionId = $answer['question_id'];
            $selectedAnswerId = $answer['selected_answer'];
            $userId = $answer['userId'];
    
            $question = $this->entityManager->getRepository(Question::class)->find($questionId);
    
            if (!$question) {
                return new JsonResponse(['message' => 'Question not found'], 404);
            }

            $correctAnswer = false;
            foreach ($question->getSuggests() as $suggest) {

                if ($suggest->isResponseExpected()) {
                    $suggestAllAnswers[] = [
                        'id' => $suggest->getId(),
                        'content' => $suggest->getContent(),
                        'responseExpected' => $suggest->isResponseExpected()
                    ];
                }

                if ($suggest->getId() === $selectedAnswerId && $suggest->isResponseExpected()) {
                    $correctAnswer = true;
                    break;
                }
            }
        
            if ($correctAnswer) {
                $score++;
            }
        }
    
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        $histoQuizz = new HistoQuizz();
        $histoQuizz->addUser($user);
        $histoQuizz->addQuizz($quizz); 
        $histoQuizz->setScore($score);
        $histoQuizz->setDate(new \DateTimeImmutable());
    
        $this->entityManager->persist($histoQuizz);
        $this->entityManager->flush();
    
       return new JsonResponse([
        'message' => 'Your response has been added successfully', 
        'score' => $score
        ], 200);
    }

    #[Route('/quizzes/{quizzId}', name: 'delete-quizz', methods: ['DELETE'])]
    public function deleteQuizzResponse( int $quizzId, QuizzRepository $questionRepository): Response
    {
        $quizz = $questionRepository->find($quizzId);
       
        if (!$quizz) {
            return new Response("Quizz not found", 404);
        }

        $this->entityManager->remove($quizz);
        $this->entityManager->flush();

        return new Response('Quizz deleted successfully', Response::HTTP_OK);
    }

}
