<?php

namespace App\Controller\Back;

use App\Entity\Brand;
use App\Form\BrandType;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/brand')]
final class BrandController extends AbstractController
{
    #[Route('', name: 'app_brand', methods: ['GET'])]
    public function index(BrandRepository $brandRepository): Response
    {
        return $this->render('back/brand/index.html.twig', [
            'brands' => $brandRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_brand_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $brand = new Brand();
        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($brand);
            $entityManager->flush();

            $this->addFlash('success', 'La marque a été créée.');

            return $this->redirectToRoute('app_brand', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/brand/new.html.twig', [
            'brand' => $brand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_brand_show', methods: ['GET'])]
    public function show(Brand $brand): Response
    {
        return $this->render('back/brand/show.html.twig', [
            'brand' => $brand,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_brand_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Brand $brand, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La marque a été modifiée.');

            return $this->redirectToRoute('app_brand', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/brand/edit.html.twig', [
            'brand' => $brand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_brand_delete', methods: ['POST'])]
    public function delete(Request $request, Brand $brand, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$brand->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Le jeton de sécurité est invalide.');
        }

        if ($brand->getProducts()->count() > 0) {
            $this->addFlash('danger', 'Cette marque ne peut pas être supprimée car elle est associée à des produits.');

            return $this->redirectToRoute('app_brand', [], Response::HTTP_SEE_OTHER);
        }

        $entityManager->remove($brand);
        $entityManager->flush();
        $this->addFlash('success', 'La marque a été supprimée.');

        return $this->redirectToRoute('app_brand', [], Response::HTTP_SEE_OTHER);
    }
}
