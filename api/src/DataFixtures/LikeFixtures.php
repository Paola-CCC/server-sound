<?php

namespace App\DataFixtures;

use App\Repository\UserRepository;
use App\Repository\ForumRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LikeFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private ForumRepository $forumRepository,
        private UserRepository $userRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();
        $forums = $this->forumRepository->findAll();

        foreach ($forums as $forum) {

  
               for ($i = 0; $i < mt_rand(0, 15); $i++) {
                    // dd(mt_rand(0, count($users)-1) < count($users) / 2);
                    if(mt_rand(0, count($users)-1) > count($users) / 4) {
                        $forum->addLike(
                            $users[mt_rand(0, count($users) - 1)]
                            );
                        
                    } else {
                        $forum->addDisLike(
                            $users[mt_rand(0, count($users) - 1)]
                         );
                    }  
                
                }
             
          

           
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ForumFixtures::class
        ];
    }
}
