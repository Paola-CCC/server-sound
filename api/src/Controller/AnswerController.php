<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Forum;
use App\Entity\Answer;
use App\Repository\AnswerRepository;
use App\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;



class AnswerController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/forums/{forumId}/new-response', name: 'add-response-forum-by-id', methods: ['POST'])]
    public function answerForum(int $forumId, ForumRepository $forumRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        $forumResponse = new Answer();
        
        $authorId = $data['author']['id'];
        $author = $this->entityManager->getRepository(User::class)->find($authorId);
        if (!$author) {
            return new Response("Author not found", 404);
        }

        $forum = $forumRepository->find($forumId);
        if (!$forum) {
            return new JsonResponse('Forum not found', 404);
        }

        $forumResponse->setAuthor($author);
        $forumResponse->setForum($forum);
        $forumResponse->setContent($data['content']);
        $forumResponse->setCreatedAt(new DateTimeImmutable());

        $entityManager->persist($forumResponse);
        $entityManager->flush();

        return new Response('Your response has been added successfully', Response::HTTP_CREATED);
    }

    #[Route('/forums/{forumId}/responses', name: 'responses-forum-by-id', methods: ['GET'])]
    public function getCommentsOfCourse(int $forumId, ForumRepository $forumRepository, AnswerRepository $answerRepository, SerializerInterface $serializer): Response
    {
        $forum = $forumRepository->find($forumId);
        if (!$forum) {
            return new JsonResponse('Forum not found', 404);
        }
    
        $answers = $forum->getResponses();
        $serializedComments = $serializer->serialize($answers, 'json', ['groups' => ['forum_answer'], 'datetime_format' => 'Y-m-d H:i:s']);
        return new JsonResponse($serializedComments, 200, [], true);
    }

    #[Route('response/{responseId}', name: 'delete-response', methods: ['DELETE'])]
    
    public function deleteResponse(int $responseId, AnswerRepository $answerRepository): Response
    {
        $forumResponse = $answerRepository->find($responseId);
        if (!$forumResponse) {
            return new Response("Response not found", 404);
        }

        $this->entityManager->remove($forumResponse);
        $this->entityManager->flush();

        return new Response('Your response in this forum is deleted successfully', Response::HTTP_OK);
    }

}
