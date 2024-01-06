<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Instrument;
use App\Entity\Subscription;
use Symfony\Component\Mime\Email;


class AuthController extends AbstractController
{
    private $jwtManager;
    private $userRepository;
    private $passwordHasher;
    private $entityManager;
    private $mailer; 
    private $doctrine; 

    public function __construct(ManagerRegistry $doctrine, 
    UserPasswordHasherInterface $passwordHasher, 
    JWTTokenManagerInterface $jwtManager, 
    EntityManagerInterface $entityManager, 
    MailerInterface $mailer ,
    UserRepository $userRepository)
    {
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }


    #[Route('/api/register', name: 'app_user_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $roles = $data['roles'] ?? '' ;
        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setUserName($data['firstName'] . ' ' . $data['lastName']);
        $user->setEmail($data['email']);

        if (str_contains($data['email'], '@saline') || $roles === 'ROLE_ADMIN' ) {
            $user->setRoles(['ROLE_ADMIN']);
        } elseif ( $roles && $roles === 'ROLE_SUPER_ADMIN' && str_contains($data['email'], '@saline-dev') ) {
            $user->setRoles(['ROLE_SUPER_ADMIN']);
        } elseif ( $roles && $roles === 'ROLE_PROFESSOR') {
            $user->setRoles(['ROLE_PROFESSOR']);
        } else {
            $user->setRoles(['ROLE_USER']);
        };

        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPhoto('https://64.media.tumblr.com/483ffa0f5c177b4c789e34b1ccc80399/51482898b4695066-0c/s1280x1920/76cd94397a3d0ad8d26cc7ab1cc97f3573ea006c.jpg');

        $instruments = $data['instruments'];

        foreach ($instruments as $instrumentName) {
            $instrument = $this->entityManager->getRepository(Instrument::class)->findOneBy(['id' => $instrumentName]);
            $user->addInstrument($instrument);
        }

        if ($data['password'] && $data['password'] !== null) {

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $data['password']
            );
            $user->setPassword($hashedPassword);
            $this->entityManager->persist($user);
            $this->entityManager->flush();


            //     $email = (new Email())
            //     ->from('support@salineroyalacademie.com')
            //     ->to($user->getEmail())
            //     ->subject('Welcome to Our Website')
            //     ->html(
            //         $this->renderView(
            //             'email/index.html.twig',
            //             ['user' => $user]
            //         )
            //     );

            // $this->mailer->send($email); 

            $token = $this->jwtManager->create($user);
    
            return new JsonResponse(['token' => $token], 201);
        } else {
            return new JsonResponse(['error' => 'Password is required'], 400);
        }
    }
}