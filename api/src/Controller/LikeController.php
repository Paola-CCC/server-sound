<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LikeController extends AbstractController
{
    #[Route('forum/{id}/like/{userId}', name: 'like.user')]
    public function getLikedByUser(Forum $forum, UserRepository $userRepository, EntityManagerInterface $manager, int $userId): Response
    {
        $user = $userRepository->find($userId);

        if($forum->isLikedByUser($user)) {
            return new JsonResponse([
                'likedByUser' => true,
                'dislikedByUser' => false
            ]);
        }

        if($forum->isDislikedByUser($user)) {
            return new JsonResponse([
                'likedByUser' => false,
                'dislikedByUser' => true
            ]);
        }

        return new JsonResponse([
            // 'message' => 'Le like a été ajouté.',
            // 'nbLikes' => $forum->howManyLikes(),
            'likedByUser' => false,
            'dislikedByUser' => false
        ]);
    }

    #[Route('/like/{userId}/forum/{id}', name: 'like.post')]
    public function like(Forum $forum, UserRepository $userRepository, EntityManagerInterface $manager, int $userId): Response
    {
        $user = $userRepository->find($userId);

        if($forum->isLikedByUser($user)) {
            $forum->removeLike($user);
            $manager->flush();

            return new JsonResponse([
                'message' => 'Le like a été supprimé.',
                'nbLikes' => $forum->howManyLikes(),
                'likedByUser' => false
            ]);
        }

        $forum->addLike($user);
        $manager->flush();

        return new JsonResponse([
            'message' => 'Le like a été ajouté.',
            'nbLikes' => $forum->howManyLikes(),
            'likedByUser' => true
        ]);
    }

    #[Route('/dislike/{userId}/forum/{id}', name: 'dislike.post')]
    public function dislike(Forum $forum, UserRepository $userRepository, EntityManagerInterface $manager, int $userId): Response
    {
        $user = $userRepository->find($userId);

        if($forum->isDislikedByUser($user)) {
            $forum->removeDislike($user);
            $manager->flush();

            return new JsonResponse([
                'message' => 'Le dislike a été supprimé.',
                'nbDislikes' => $forum->howManyDislikes(),
                'dislikedByUser' => false
            ]);
        }

        $forum->addDislike($user);
        $manager->flush();

        return new JsonResponse([
            'message' => 'Le dislike a été ajouté.',
            'nbDislikes' => $forum->howManyDislikes(),
            'dislikedByUser' => true
        ]);
    }
}