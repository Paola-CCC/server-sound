<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\User;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ForumRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ForumController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/forums', name: 'forum_list', methods: ['GET'])]
    public function getForumList(ForumRepository $forumRepository): JsonResponse
    {
        $forumsList = $forumRepository->findAll();

        foreach ($forumsList as $forum) {
            $answersCount = $forum->getResponses()->count();
            $forum->setAnswersCount($answersCount);
            $likesCount = $forum->howManyLikes();
            $forum->setLikesCount($likesCount);
            $dislikesCount = $forum->howManyDislikes();
            $forum->setDislikesCount($dislikesCount);
        }
        $response = $this->json($forumsList, 200, [], ['groups' => ['forum','forum_user_id', 'forum_answers_count','category', 'user_forum_like', 'likes_forum_count', 'dislikes_forum_count'],'datetime_format' => 'Y-m-d H:i:s']);
        return $response;
    }

    #[Route('forums-category/{categoryId}', name: 'find_forums_category', methods: ['GET'])]
    public function findByCategory(ForumRepository $forumRepository , SerializerInterface $serializer, string $categoryId): JsonResponse
    {

        $forumWithCategory = $forumRepository->findByCategoryId($categoryId);

        foreach ($forumWithCategory as $forum) {
            $answersCount = $forum->getResponses()->count();
            $forum->setAnswersCount($answersCount);
            $likesCount = $forum->howManyLikes();
            $forum->setLikesCount($likesCount);
            $dislikesCount = $forum->howManyDislikes();
            $forum->setDislikesCount($dislikesCount);
        }

        $data = $serializer->serialize($forumWithCategory, 'json', ['groups' => ['forum','forum_user_id', 'forum_answers_count','category', 'user_forum_like', 'likes_forum_count', 'dislikes_forum_count']]);
    
        return new JsonResponse($data, 200, [], true);
    }


    #[Route('forums-subject/{subjectName}', name: 'find_forums_subject', methods: ['GET'])]
    public function findBySubject(ForumRepository $forumRepository , SerializerInterface $serializer, string $subjectName): JsonResponse
    {

        $forumWithCategory = $forumRepository->findBySubjectName($subjectName);

        foreach ($forumWithCategory as $forum) {
            $answersCount = $forum->getResponses()->count();
            $forum->setAnswersCount($answersCount);
            $likesCount = $forum->howManyLikes();
            $forum->setLikesCount($likesCount);
            $dislikesCount = $forum->howManyDislikes();
            $forum->setDislikesCount($dislikesCount);
        }

        $data = $serializer->serialize($forumWithCategory, 'json', ['groups' => ['forum','forum_user_id', 'forum_answers_count','category', 'user_forum_like', 'likes_forum_count', 'dislikes_forum_count']]);
    
        return new JsonResponse($data, 200, [], true);
    }


    #[Route('/forums/{forumId}', name: 'forum_by_id', methods: ['GET'])]
    public function getForumById(int $forumId, ForumRepository $forumRepository): JsonResponse
    {
        $forum = $forumRepository->find($forumId);
        if (!$forum) {
            return new JsonResponse('Forum not found', 404);
        }
        $likesCount = $forum->howManyLikes();
        $forum->setLikesCount($likesCount);
        $dislikesCount = $forum->howManyDislikes();    
        $forum->setDislikesCount($dislikesCount);
        
        $response = $this->json($forum, 200, [], ['groups' => ['forum','forum_user_id', 'forum_category', 'category', 'forum_answer', 'likes_forum_count', 'dislikes_forum_count'], 'datetime_format' => 'Y-m-d H:i:s']);
        
        return $response;
    }

    #[Route('/new-forum', name: 'new_forum', methods: ['POST'])]
    public function newForum(Request $request, EntityManagerInterface $entityManager): Response    
    {
        $data = json_decode($request->getContent(), true);
        $forum = new Forum();
        
        $authorId = $data['author']['id'];
        $author = $this->entityManager->getRepository(User::class)->find($authorId);
        if (!$author) {
            return new Response("Author not found", 404);
        }
        $forum->setAuthor($author);
        $forum->setSubject($data['subject']);
        $forum->setDescription($data['description']);
        $forum->setCreatedAt(new DateTimeImmutable());
        foreach($data['category'] as $value){
            $categoryName = $value;
            $category = $this->entityManager->getRepository(Category::class)->findOneBy(['id' => $categoryName]);
            if (!$category) {
                return new Response("Category not found", 404);
            }
            $forum->addCategory($category);
        }

        $entityManager->persist($forum);
        $entityManager->flush();

        $response = $this->json($forum, 200, [], ['groups' => ['forum','forum_user_id', 'category'],'datetime_format' => 'Y-m-d H:i:s']);
        return $response;
    }

    #[Route('/forums/{forumId}', name: 'delete-forum', methods: ['DELETE'])]
    public function deleteResponse( int $forumId, ForumRepository $forumRepository): Response
    {
        $forum = $forumRepository->find($forumId);
       
        if (!$forum) {
            return new Response("Forum not found", 404);
        }

        $this->entityManager->remove($forum);
        $this->entityManager->flush();

        return new Response('Topic forum deleted successfully', Response::HTTP_OK);
    }

    #[Route('/forums-delete-many', name: 'forums_delete_many', methods: ['DELETE'])]
    public function deleteForumsMany(Request $request, ForumRepository $forumRepository): JsonResponse
    {
        $forumsIds = $request->query->get('forumsIds');

        if (!$forumsIds) {
            return new JsonResponse(['message' => 'No forums IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $forumsIds = json_decode($forumsIds, true);

        foreach ($forumsIds as $forumId) {
            $forum = $forumRepository->find($forumId);

            if ($forum) {
                $this->entityManager->remove($forum);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Forums deleted successfully'], 200);
    }
}
