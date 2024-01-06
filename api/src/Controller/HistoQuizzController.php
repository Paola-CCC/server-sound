<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoQuizzController extends AbstractController
{
    #[Route('/histo/quizz', name: 'app_histo_quizz')]
    public function index(): Response
    {
        return $this->render('histo_quizz/index.html.twig', [
            'controller_name' => 'HistoQuizzController',
        ]);
    }
}
