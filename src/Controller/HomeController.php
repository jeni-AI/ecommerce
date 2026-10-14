<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    
    public function index(): Response
    {
        $name = 'jennissa';

        $montableau = ['mama','tata','toto'];

        return $this->render('front/home/index.html.twig',[
            'name'=> $name,

            'montableau'=> $montableau,
             
            ]); 
    }
}


