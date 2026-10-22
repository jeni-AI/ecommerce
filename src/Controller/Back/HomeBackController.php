<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class HomeBackController extends AbstractController
{
    #[Route('', name: 'app_home_back')]
    public function index(): Response
    {
        return $this->render('back/home_back/index.html.twig', [
            'controller_name' => 'HomeBackController',
        ]);
    }
}
