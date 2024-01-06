<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Repository\SubscriptionRepository;

#[Route('/subscriptions')]
class SubscriptionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/new', name: 'new_subscription', methods: ['POST'])]
    public function newSubscription(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $subscription = new Subscription();
        $subscription->setName($data['name']);
        $subscription->setDescription($data['description']);
        $subscription->setPrice($data['price']);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return new Response('Subscription created successfully', Response::HTTP_CREATED);
    }

    #[Route('/{subscriptionId}', name: 'subscription_update', methods: ['PUT'])]
    public function updateSubscription(int $subscriptionId, SubscriptionRepository $subscriptionRepository, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $subscription = $subscriptionRepository->find($subscriptionId);

        if (!$subscription) {
            return new JsonResponse(['message' => 'Subscription not found'], 404);
        }

        $subscription->setName($data['name']);
        $subscription->setDescription($data['description']);
        $subscription->setPrice($data['price']);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return new Response('Subscription updated successfully', Response::HTTP_OK);
    }

    #[Route('/', name: 'subscription_list', methods: ['GET'])]
    public function getSubscriptionList(SubscriptionRepository $subscriptionRepository, SerializerInterface $serializer): JsonResponse
    {
        $subscriptionsList = $subscriptionRepository->findAll();

        $serializedSubscriptions = $serializer->serialize($subscriptionsList, 'json', ['groups' => ['subscription']]);

        return new JsonResponse($serializedSubscriptions, 200, [], true);
    }

    #[Route('/{subscriptionId}', name: 'subscription_by_id', methods: ['GET'])]
    public function getSubscriptionById(int $subscriptionId, SubscriptionRepository $subscriptionRepository, SerializerInterface $serializer): JsonResponse
    {
        $subscription = $subscriptionRepository->find($subscriptionId);

        if (!$subscription) {
            return new JsonResponse(['message' => 'Subscription not found'], 404);
        }

        $serializedSubscription = $serializer->serialize($subscription, 'json', ['groups' => ['subscription']]);

        return new JsonResponse($serializedSubscription, 200, [], true);
    }

    #[Route('/{subscriptionId}', name: 'subscription_delete', methods: ['DELETE'])]
    public function deleteSubscription(int $subscriptionId, SubscriptionRepository $subscriptionRepository, SerializerInterface $serializer): JsonResponse
    {
        $subscription = $subscriptionRepository->find($subscriptionId);

        if (!$subscription) {
            return new JsonResponse(['message' => 'Subscription not found'], 404);
        }

        $this->entityManager->remove($subscription);
        $this->entityManager->flush();

        return new Response('Instrument deleted successfully', Response::HTTP_OK);
    }
}
