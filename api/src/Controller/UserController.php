<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Instrument;
use App\Entity\Subscription;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;



class UserController extends AbstractController
{
    private $doctrine;
    private $entityManager;
    private $mailer; 

    public function __construct(ManagerRegistry $doctrine, JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager, MailerInterface $mailer )
    {
        $this->doctrine = $doctrine;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        
    }

    #[Route('/users-all', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $usersList = $userRepository->findAll();
        $jsonUsersList = $serializer->serialize($usersList, 'json', ['groups' => ['user']]);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/professors', name: 'app_prof_index', methods: ['GET'])]
    public function getAllProfessors(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $usersList = $userRepository->findAll();
        $professorList = array();
        foreach ($usersList as $user) {
            if($user->getRoles()[0] == 'ROLE_PROFESSOR'){
                array_push($professorList, $user);
            }
        }
        $jsonUsersList = $serializer->serialize($professorList, 'json', ['groups' => ['user']]);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, ['accept' => 'json'], true);
    }


    #[Route('/students', name: 'app_students_index', methods: ['GET'])]
    public function getAllStudents(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $usersList = $userRepository->findAll();
        $studentsList = array();
        foreach ($usersList as $user) {
            if($user->getRoles()[0] == 'ROLE_USER'){
                array_push($studentsList, $user);
            }
        }
        $jsonUsersList = $serializer->serialize($studentsList, 'json', ['groups' => ['user']]);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/entreprise', name: 'app_entreprise_index', methods: ['GET'])]
    public function getAllEntreprise(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $usersList = $userRepository->findAll();
        $studentsList = array();
        foreach ($usersList as $user) {
            if($user->getRoles()[0] == 'ROLE_ADMIN' || $user->getRoles()[0] == 'ROLE_SUPER_ADMIN' ){
                array_push($studentsList, $user);
            }
        }
        $jsonUsersList = $serializer->serialize($studentsList, 'json', ['groups' => ['user']]);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, ['accept' => 'json'], true);
    }


    #[Route('/user/{userId}', name: 'app_user_show', methods: ['GET'])]
    public function show(int $userId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $serializedUser = $serializer->serialize($user, 'json', ['groups' => ['user', 'user_instruments', 'instrument', 'user_coursesGiven', 'user_courses', 'course', 'subscription']]);

        return new JsonResponse($serializedUser, 200, [], true);

    }

    #[Route('user/{id}/edit', name: 'app_user_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if(isset($data['firstName'])){
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setEmail($data['email']);
            if($data['role'] != ""){
                $role = $data['role'];
                if ($role == 'admin') {
                    $user->setRoles(['ROLE_ADMIN']);
                } elseif ($role == 'professor') {
                    $user->setRoles(['ROLE_PROFESSOR']);
                } else {
                    $user->setRoles(['ROLE_USER']);
                }
            }
    
            $instruments = $data['instruments'];
    
            foreach ($instruments as $instrumentName) {
                $instrument = $this->entityManager->getRepository(Instrument::class)->findOneBy(['name' => $instrumentName]);
                if (!$instrument) {
                    return new JsonResponse(['message' => 'Instrument not found'], 404);
                };
                $user->addInstrument($instrument);
            }
    
            foreach ($user->getInstruments() as $instrument){
                if(!in_array($instrument->getName(), $instruments)){
                    $user->removeInstrument($instrument);
                }
            }
        }

        if(isset($data['subscription'])){
            $subscriptionId = $data['subscription']['id'];
            $subscription = $this->entityManager->getRepository(Subscription::class)->find($subscriptionId);
            $user->setSubscription($subscription);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User updated successfully'], 200);
    }

    #[Route('/users-delete/{userID}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(int $userID): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($userID);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User deleted successfully'], 200);
    }



    #[Route('/users-delete-many', name: 'users_delete_many', methods: ['DELETE'])]
    public function deleteCourseMany(Request $request, UserRepository $userRepository): JsonResponse
    {
        $usersIds = $request->query->get('usersIds');
    
        if (!$usersIds) {
            return new JsonResponse(['message' => 'No users IDs provided'], Response::HTTP_BAD_REQUEST);
        }
    
        $usersIds = json_decode($usersIds, true);
    
        foreach ($usersIds as $userId) {
            $user = $userRepository->find($userId);
    
            if ($user) {
                $this->entityManager->remove($user);
            }
        }
    
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Users deleted successfully'], 200);
    }
    
}
