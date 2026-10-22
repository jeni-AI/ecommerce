<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function customerLogin(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('back/login/index.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/admin/login', name: 'app_admin_login', methods: ['GET', 'POST'])]
    public function adminLogin(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('back/login/admin.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
    }

    #[Route('/admin/logout', name: 'app_admin_logout', methods: ['GET'])]
    public function adminLogout(): void
    {
    }
}
