<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use IntlDateFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class MessageController extends AbstractController
{
    private $serializer;
    private $formatter;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        $this->formatter->setPattern('dd/MM/yyyy HH:mm');
    }

    #[Route('/all-messages', name: 'message_index', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {

        $messages = $doctrine
            ->getRepository(Message::class)
            ->findAll();

        $data = [];

        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'content' => $message->getContentText(),
                'author' => $message->getAuthor(),
                'createdAt' => $message->getSentDate()
            ];
        }

        $json = $this->serializer->serialize($data, 'json', ['groups' => ['messages'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);

        return new JsonResponse($json, 200, [], true);
    }


    #[Route('/new-message', name: 'message_create', methods: ['POST'])]
    public function create(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $author = $doctrine->getRepository(User::class)->find($data['userId']);

        if (!$author) {
            return new Response("Author not found", 404);
        }

        $conversationId = $doctrine->getRepository(Conversation::class)->find($data['conversationId']);

        $message = new Message();
        $message->setAuthor($author);
        $message->setContentText($data['content_Text']);
        $message->setSentDate(new \DateTimeImmutable());
        $message->setConversation($conversationId);
        $entityManager->persist($message);
        $entityManager->flush();

        $responseData = [
            'id' => $message->getId(),
            'username' => $message->getAuthor(),
            'content' => $message->getContentText(),
            'date' =>  $this->formatter->format($message->getSentDate())
        ];

        $json = $this->serializer->serialize($responseData, 'json', ['groups' => ['messages'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }


    #[Route('/message/{id}', name: 'message_show', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, SerializerInterface $serializer, int $id): JsonResponse
    {
        $message = $doctrine->getRepository(Message::class)->find($id);
        if (!$message) {
            return $this->json('No message found for id ' . $id, 404);
        }

        $data =  [
            'id' => $message->getId(),
            'content' => $message->getContentText(),
            'author' => $message->getAuthor(),
            'conversation' => $message->getConversation()
        ];

        $json = $serializer->serialize($data, 'json', ['groups' => ['messages'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }


    #[Route('/delete-message/{id}', name: 'message_delete', methods: ['DELETE'])]
    public function removeMessage(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $messageRepository = $entityManager->getRepository(Message::class);
        $message = $messageRepository->find($id);

        if (!$message) {
            return new JsonResponse('Message not found', 404);
        }

        $entityManager->remove($message);
        $entityManager->flush();

        return new JsonResponse('Message removed successfully');
    }
}
