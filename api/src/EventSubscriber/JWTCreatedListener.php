<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsEventListener(event: "lexik_jwt_authentication.on_jwt_created", method: 'onJWTCreated')]
final class JWTCreatedListener
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();     
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $email = $user->getUserIdentifier();

        // Recherchez l'utilisateur par son adresse e-mail dans la base de données
        $userRepository = $this->doctrine->getRepository(User::class);
        $userEntity = $userRepository->findOneBy(['email' => $email]);

        if ($userEntity instanceof User) {
            // Récupérez l'ID de l'utilisateur et ajoutez-le au payload du JWT
            $userId = $userEntity->getId();
            $payload['userId'] = $userId;
            $payload['username'] = $userEntity->getUsername();
        }

        $event->setData($payload);

    }
}