<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Entity\RefStatutProduit;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('back/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
        
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveUploadedImage($product, $form->get('imageFile')->getData());
            $product->setStatusRecord(
                (new RefStatutProduit())
                    ->setProduct($product)
                    ->setStatus(RefStatutProduit::HIDDEN)
            );
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a été créé et reste masqué jusqu’à sa publication.');

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/publication', name: 'app_product_toggle_publication', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function togglePublication(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid(
            'toggle-product-publication'.$product->getId(),
            $request->getPayload()->getString('_token')
        )) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['message' => 'Le jeton de sécurité est invalide.'], Response::HTTP_FORBIDDEN);
            }

            throw $this->createAccessDeniedException('Le jeton de sécurité est invalide.');
        }

        $status = $product->getCurrentStatus();
        if ($status === null) {
            $status = (new RefStatutProduit())->setProduct($product);
            $product->setStatusRecord($status);
        }

        $isPublished = $status->getStatus() !== RefStatutProduit::PUBLISHED;
        $status
            ->setStatus($isPublished ? RefStatutProduit::PUBLISHED : RefStatutProduit::HIDDEN)
            ->setCreatedAt(new \DateTimeImmutable());

        $entityManager->flush();
        $message = $isPublished
            ? 'Le produit a été publié dans le catalogue.'
            : 'Le produit a été masqué du catalogue.';

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => $status->getStatus(),
                'label' => $status->getLabel(),
                'published' => $isPublished,
                'message' => $message,
            ]);
        }

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('back/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveUploadedImage($product, $form->get('imageFile')->getData());
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    private function saveUploadedImage(Product $product, mixed $imageFile): void
    {
        if (!$imageFile instanceof UploadedFile) {
            return;
        }

        $filename = bin2hex(random_bytes(16)).'.'.$imageFile->guessExtension();
        $imageFile->move($this->getParameter('kernel.project_dir').'/public/uploads', $filename);
        $product->setImage($filename);
    }
}
