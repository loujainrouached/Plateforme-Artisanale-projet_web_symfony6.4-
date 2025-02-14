<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AtelierController extends AbstractController
{
    #[Route('/atelier', name: 'app_atelier')]
    public function index(): Response
    {
        return $this->render('atelier/index.html.twig', [
            'controller_name' => 'AtelierController',
        ]);
    }


    #[Route('/backatelier', name: 'back_atelier')]
    public function back(): Response
    {
        return $this->render('atelier/backatelier.html.twig', [
            'controller_name' => 'AtelierController',
        ]);
    }
}
