<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Panier;
use App\Form\RegisterUserType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RegisterBackController extends AbstractController
    {
            private LoggerInterface $logger;

            public function __construct(LoggerInterface $logger)
            {
                $this->logger = $logger;
            }
                #[Route('/admin/users', name: 'app_users', methods: ['GET'])]

                public function index(UserRepository $userRepository):Response
                {
                    $users = $userRepository->findAll();

                    return $this->render('back/register/index.html.twig',[
                        'users'=> $users, 
                        
                    ]);

                }



            #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]

            public function register(
                Request $request,
                EntityManagerInterface $entityManager,
                RoleRepository $roleRepository
            ): Response

            {

            $form = null;
            $user = new User();
                $form = $this->createForm(RegisterUserType::class, $user);
            try{
                    
                    $form->handleRequest($request);
                    
                    if ($form->isSubmitted() && $form->isValid()) 
                    {
                        $role = $roleRepository->findOneBy(['code' => 'ROLE_CLIENT']);
                        if ($role === null) {
                            throw new \LogicException('Le rôle client ROLE_CLIENT est introuvable.');
                        }

                        $user->setRole($role);
                        $user->setRoles([$role->getCode()]);
                        $client = (new Client())->setUser($user);
                        (new Panier())->setClient($client);
                        $entityManager->persist($user);
                        $entityManager->flush();
                        $this->addFlash('success', 'Votre compte client a été créé.');

                        return $this->redirectToRoute('app_login');
                    }
                } catch(\Exception $e){
                    $this->addFlash('danger','Une erreur est survenue lors de la creation de votre compte veuillez réessayer plus tard');
                    $this->logger->error("Erreur lors de l'inscription d' un utilisateur",['exception'=> $e]);
                }

                return $this->render('back/register/register.html.twig',[
                    'registerForm' => $form->createView()
                ]);
            }

            #[Route('/admin/users/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
            public function newAdminUser(
                Request $request,
                EntityManagerInterface $entityManager
            ): Response {
                $user = new User();
                $form = $this->createForm(RegisterUserType::class, $user, [
                    'allow_role_selection' => true,
                ]);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $role = $user->getRole();
                    if ($role === null || !in_array($role->getCode(), ['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_CLIENT'], true)) {
                        throw new \LogicException('Le rôle sélectionné est invalide.');
                    }

                    $user->setRoles([$role->getCode()]);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->addFlash('success', 'Le compte a été créé.');

                    return $this->redirectToRoute('app_users');
                }

                return $this->render('back/register/new_admin_user.html.twig', [
                    'registerForm' => $form->createView(),
                ]);
            }

            #[Route('/admin/users/{id}/modifier', name: 'app_user_edit', methods: ['GET', 'POST'])]
            public function edit(
                Request $request,
                User $user,
                EntityManagerInterface $entityManager,
                UserPasswordHasherInterface $passwordHasher
            ): Response {
                $form = $this->createForm(RegisterUserType::class, $user, [
                    'edit_mode' => true,
                    'allow_role_selection' => true,
                ]);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $password = $form->get('password')->getData();
                    if (is_string($password) && $password !== '') {
                        $user->setPassword($passwordHasher->hashPassword($user, $password));
                    }

                    if ($user->getRole() !== null) {
                        $user->setRoles([$user->getRole()->getCode()]);
                    }

                    $entityManager->flush();
                    $this->addFlash('success', 'Les informations de l’utilisateur ont été modifiées.');

                    return $this->redirectToRoute('app_users');
                }

                return $this->render('back/register/edit.html.twig', [
                    'user' => $user,
                    'form' => $form,
                ]);
            }

            #[Route('/admin/users/{id}/supprimer', name: 'app_user_delete', methods: ['POST'])]
            public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
            {
                if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
                    throw $this->createAccessDeniedException('Le jeton de sécurité est invalide.');
                }

                $entityManager->remove($user);
                $entityManager->flush();
                $this->addFlash('success', 'L’utilisateur a été supprimé.');

                return $this->redirectToRoute('app_users');
            }

            #[Route('/admin/users/{id}/connexion', name: 'app_user_toggle_validity', methods: ['POST'])]
            #[IsGranted('ROLE_ADMIN')]
            public function toggleValidity(
                Request $request,
                User $user,
                EntityManagerInterface $entityManager
            ): JsonResponse {
                if (!$this->isCsrfTokenValid('toggle-user'.$user->getId(), $request->getPayload()->getString('_token'))) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Le jeton de sécurité est invalide. Rechargez la page et réessayez.',
                    ], Response::HTTP_FORBIDDEN);
                }

                $currentUser = $this->getUser();
                if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Vous ne pouvez pas désactiver votre propre compte.',
                    ], Response::HTTP_CONFLICT);
                }

                $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);
                if ($isAdmin && $user->isValid() === true) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Un compte administrateur ne peut pas être désactivé.',
                    ], Response::HTTP_CONFLICT);
                }

                $user->setIsValid(!$user->isValid());
                $entityManager->flush();

                return $this->json([
                    'success' => true,
                    'isValid' => $user->isValid(),
                    'message' => $user->isValid()
                        ? 'La connexion de l’utilisateur a été activée.'
                        : 'La connexion de l’utilisateur a été désactivée.',
                ]);
            }

        }
