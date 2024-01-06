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
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ConversationController extends AbstractController
{
    private $serializer;
    private $formatter;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        $this->formatter->setPattern('dd/MM/yyyy HH:mm');
    }

    #[Route('/all-conversations', name: 'app_conversation', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {

        $allConversation = $doctrine
            ->getRepository(Conversation::class)
            ->findAll();

        $data = [];

        foreach ($allConversation as $conversation) {
            $data[] = [
                'id' => $conversation->getId(),
                'userOne' => $conversation->getUserOne(),
                'userTwo' => $conversation->getUserTwo(),
                'date' =>  $this->formatter->format($conversation->getCreatedAt()),
                'message' => $conversation->getMessages()
            ];
        }
        $json = $this->serializer->serialize($data, 'json', ['groups' => ['messages', 'conversation'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/new-conversation', name: 'new_conversation', methods: ['POST'])]
    public function create(ManagerRegistry $doctrine, HttpFoundationRequest $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $authorOneId = $doctrine->getRepository(User::class)->find($data['userOneId']);
        $authorTwoId = $doctrine->getRepository(User::class)->find($data['userProfessorId']);
        $allResults = [];

        if (!$authorOneId || !$authorTwoId) {
            $textError = "Un des utilisateurs n'existe pas";
            return new JsonResponse($textError, 404, [], true);
        }

        $conversation = $entityManager->getRepository(Conversation::class)->findByUsers($data['userOneId'], $data['userProfessorId']);

        // Si l'utilisateur n'existe pas
        if(empty($conversation)) {
            $conversation = new Conversation();
            $conversation->setUserOne($authorOneId);
            $conversation->setUserTwo($authorTwoId);
            $conversation->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($conversation);
            $entityManager->flush();
        }

        foreach ($conversation as $conversation) {
        
            $allResults[] = [
                'conversationId' => $conversation->getId(),
                'content' => $conversation->getMessages(),
                'authorOneId' => $conversation->getUserOne(),
                'authorTwoId' => $conversation->getUserTwo()
            ];
        }

        $json = $this->serializer->serialize($allResults, 'json', ['groups' => ['messages', 'conversation'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/conversation-professor', name: 'conversation_show_prof', methods: ['POST'])]
    public function showAllForProfessor(ManagerRegistry $doctrine,HttpFoundationRequest $request, SerializerInterface $serializer , EntityManagerInterface $entityManager): JsonResponse
    {

        $data = json_decode($request->getContent(), true);
        $userProfessorId = $doctrine->getRepository(User::class)->find($data['userProfessorId']);

        if (!$userProfessorId) {
            $textError = "Aucun utilisateur non connu";
            return new JsonResponse($textError, 404, [], true);
        }

        $allConversationProf = $entityManager->getRepository(Conversation::class)->findByProfessor($data['userProfessorId']);
        
        if (!$allConversationProf) {
            return new JsonResponse("Il semble qu'aucune conversation n'existe pour ce professeur", 404, [], true);
        }

        $data = [];

        foreach ($allConversationProf as $conversation) {
            $data[] =  [
                'id' => $conversation->getId(),
                'userOne' => $conversation->getUserOne(),
                'userTwo' => $conversation->getUserTwo(),
                'date' =>  $this->formatter->format($conversation->getCreatedAt()),
                'message' => $conversation->getMessages()
            ];
        }

        $json = $serializer->serialize($data, 'json', ['groups' => ['messages', 'conversation'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }



    #[Route('/conversation-student', name: 'conversation_show_student', methods: ['POST'])]
    public function showAllForStudent(ManagerRegistry $doctrine,HttpFoundationRequest $request, SerializerInterface $serializer , EntityManagerInterface $entityManager): JsonResponse
    {

        $data = json_decode($request->getContent(), true);
        $userProfessorId = $doctrine->getRepository(User::class)->find($data['userOneId']);

        if (!$userProfessorId) {
            $textError = "Aucun utilisateur non connu";
            return new JsonResponse($textError, 404, [], true);
        }

        $allConversationStudents = $entityManager->getRepository(Conversation::class)->findByStudent($data['userOneId']);
        
        if (!$allConversationStudents) {
            return new JsonResponse("Il semble qu'aucune conversation n'existe pour cet utilisateur", 404, [], true);
        }

        $data = [];

        foreach ($allConversationStudents as $conversation) {
            
            $data[] =  [
                'id' => $conversation->getId(),
                'userOne' => $conversation->getUserOne(),
                'userTwo' => $conversation->getUserTwo(),
                'date' =>  $this->formatter->format($conversation->getCreatedAt()),
                'message' => $conversation->getMessages()
            ];
        }

        $json = $serializer->serialize($data, 'json', ['groups' => ['messages', 'conversation'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/conversation/{id}', name: 'conversation_show', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, SerializerInterface $serializer, int $id): JsonResponse
    {
        $conversation = $doctrine->getRepository(Conversation::class)->find($id);
        if (!$conversation) {
            return new JsonResponse("Aucune conversation n'a été trouvé", 404, [], true);
        }

        $data =  [
            'id' => $conversation->getId(),
            'userOne' => $conversation->getUserOne(),
            'userTwo' => $conversation->getUserTwo(),
            'date' =>  $this->formatter->format($conversation->getCreatedAt()),
            'message' => $conversation->getMessages()
        ];

        $json = $serializer->serialize($data, 'json', ['groups' => ['messages', 'conversation'], 'datetime_format' => 'dd/MM/yyyy HH:mm']);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/delete-conversation/{id}', name: 'delete_conversation', methods: ['DELETE'])]
    public function removeMessage(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $messageRepository = $entityManager->getRepository(Conversation::class);
        $message = $messageRepository->find($id);

        if (!$message) {
            return new JsonResponse('Conversation not found', 404);
        }

        $entityManager->remove($message);
        $entityManager->flush();

        return new JsonResponse('Conversation removed successfully');
    }
}
