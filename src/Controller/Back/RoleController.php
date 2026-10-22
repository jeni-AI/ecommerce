<?php

namespace App\Controller\Back;

use App\Entity\Role;
use App\Entity\User;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/role')]
final class RoleController extends AbstractController
{
    #[Route('', name: 'app_role', methods: ['GET']), IsGranted('ROLE_ADMIN')]
   
    public function index(RoleRepository $roleRepository): Response
    {
        return $this->render('back/role/index.html.twig', [
            'roles' => $roleRepository->findBy([], ['code' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_role_new', methods: ['GET', 'POST']), IsGranted('ROLE_ADMIN') ]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($role);
            $entityManager->flush();
            $this->addFlash('success', 'Le rôle a été créé.');

            return $this->redirectToRoute('app_role', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/role/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_show', methods: ['GET'])]
    public function show(Role $role, EntityManagerInterface $entityManager): Response
    {
        return $this->render('back/role/show.html.twig', [
            'role' => $role,
            'userCount' => $entityManager->getRepository(User::class)->count(['role' => $role]),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Role $role, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le rôle a été modifié.');

            return $this->redirectToRoute('app_role', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/role/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Role $role,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('delete'.$role->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Le jeton de sécurité est invalide.');
        }

        $userCount = $entityManager->getRepository(User::class)->count(['role' => $role]);
        if ($userCount > 0) {
            $this->addFlash('danger', 'Ce rôle ne peut pas être supprimé car il est attribué à des utilisateurs.');

            return $this->redirectToRoute('app_role', [], Response::HTTP_SEE_OTHER);
        }

        $entityManager->remove($role);
        $entityManager->flush();
        $this->addFlash('success', 'Le rôle a été supprimé.');

        return $this->redirectToRoute('app_role', [], Response::HTTP_SEE_OTHER);
    }
}
