<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Course;
use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/courses/{courseId}/comments', name: 'course_comments', methods: ['GET'])]
    public function getCommentsOfCourse(int $courseId, CourseRepository $courseRepository, SerializerInterface $serializer): Response
    {
        $course = $courseRepository->find($courseId);
    
        if (!$course) {
            return new JsonResponse(['message' => 'Course not found'], 404);
        }
    
        $comments = $course->getComments();
    
        $serializedComments = $serializer->serialize($comments, 'json', ['groups' => ['course_comments'], 'datetime_format' => 'Y-m-d H:i:s']);
    
        return new JsonResponse($serializedComments, 200, [], true);
    }
    
    #[Route('/courses/{courseId}/user/{userId}/comment', name: 'add_comment', methods: ['POST'])]
    public function addCommentToCourse(int $courseId, int $userId, Request $request, UserRepository $userRepository, CourseRepository $courseRepository): Response
    {
        $user = $userRepository->find($userId);
        $course = $courseRepository->find($courseId);

        if (!$user || !$course) {
            return new JsonResponse(['message' => 'User or Course not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $comment = new Comment();
        $comment->setContent($data['content']);
        // $comment->setTitle($data['title']);
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setUser($user);
        $comment->setCourse($course);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return new Response('Comment added successfully', Response::HTTP_CREATED);
    }

    #[Route('/courses/{courseId}/user/{userId}/comment/{commentId}', name: 'update_comment', methods: ['PUT'])]
    public function updateComment(int $courseId, int $userId, int $commentId, Request $request, UserRepository $userRepository, CourseRepository $courseRepository, CommentRepository $commentRepository): Response
    {
        $user = $userRepository->find($userId);
        $course = $courseRepository->find($courseId);
        $comment = $commentRepository->find($commentId);

        if (!$user || !$course || !$comment) {
            return new JsonResponse(['message' => 'User, Course or Comment not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $comment->setContent($data['content']);
        $comment->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return new Response('Comment updated successfully', Response::HTTP_OK);
    }

    #[Route('/courses/{courseId}/user/{userId}/comment/{commentId}', name: 'delete_comment', methods: ['DELETE'])]
    public function deleteComment(int $courseId, int $userId, int $commentId, UserRepository $userRepository, CourseRepository $courseRepository, CommentRepository $commentRepository): Response
    {
        $user = $userRepository->find($userId);
        $course = $courseRepository->find($courseId);
        $comment = $commentRepository->find($commentId);

        if (!$user || !$course || !$comment) {
            return new JsonResponse(['message' => 'User, Course or Comment not found'], 404);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return new Response('Comment deleted successfully', Response::HTTP_OK);
    }
}