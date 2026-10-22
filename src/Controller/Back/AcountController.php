<?php

namespace App\Controller\Back;

use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientProfileType;
use App\Form\PasswordUserType;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AcountController extends AbstractController
{
    #[Route('/account', name: 'app_acount')]
    #[Route('/admin/account', name: 'app_admin_acount')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('Vous devez être connecté pour consulter votre profil.');
        }

        $profileForm = $this->createForm(UserProfileType::class, $user);
        $client = null;
        $clientProfileForm = null;
        if (in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            $client = $user->getClient();
            if ($client === null) {
                $client = (new Client())->setUser($user);
                $entityManager->persist($client);
            }
            $clientProfileForm = $this->createForm(ClientProfileType::class, $client);
        }

        $profileForm->handleRequest($request);
        if ($clientProfileForm !== null) {
            $clientProfileForm->handleRequest($request);
        }

        if ($profileForm->isSubmitted()) {
            if ($profileForm->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', 'Vos informations personnelles ont été mises à jour.');

                return $this->redirectToRoute(
                    $request->attributes->get('_route') === 'app_admin_acount' ? 'app_admin_acount' : 'app_acount',
                    [],
                    Response::HTTP_SEE_OTHER
                );
            }
        } elseif ($clientProfileForm?->isSubmitted()) {
            if ($clientProfileForm->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', 'Vos coordonnées personnelles ont été mises à jour.');

                return $this->redirectToRoute(
                    $request->attributes->get('_route') === 'app_admin_acount' ? 'app_admin_acount' : 'app_acount',
                    [],
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        return $this->render('back/acount/index.html.twig', [
            'profileForm' => $profileForm->createView(),
            'clientProfileForm' => $clientProfileForm?->createView(),
        ]);
    }

    #[Route('/account/modify-password', name: 'app_acount_modify-pwd')]
    #[Route('/admin/account/modify-password', name: 'app_admin_acount_modify_pwd')]
    public function modifyPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('Vous devez être connecté pour modifier votre mot de passe.');
        }

        $form = $this->createForm(PasswordUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $actualPassword = $form->get('actualPassword')->getData();
            if (!is_string($actualPassword) || !$passwordHasher->isPasswordValid($user, $actualPassword)) {
                $form->get('actualPassword')->addError(
                    new \Symfony\Component\Form\FormError('Le mot de passe actuel est incorrect.')
                );
            } else {
                $newPassword = $form->get('plainpassword')->getData();
                if (!is_string($newPassword)) {
                    throw new \LogicException('Le nouveau mot de passe doit être une chaîne de caractères.');
                }

                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a été modifié.');

                return $this->redirectToRoute(
                    $request->attributes->get('_route') === 'app_admin_acount_modify_pwd'
                        ? 'app_admin_acount'
                        : 'app_acount'
                );
            }
        }

        return $this->render('back/acount/modify_password.html.twig', [
            'modifyPasswordForm' => $form->createView(),
            'profileRoute' => $request->attributes->get('_route') === 'app_admin_acount_modify_pwd'
                ? 'app_admin_acount'
                : 'app_acount',
        ]);
    }
}
