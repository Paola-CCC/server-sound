<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuggestController extends AbstractController
{
    #[Route('/suggest', name: 'app_suggest')]
    public function index(): Response
    {
        return $this->render('question/index.html.twig', [
            'controller_name' => 'SuggestController',
        ]);
    }
}
