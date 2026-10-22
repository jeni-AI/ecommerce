<?php

namespace App\Controller\Front;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produit')]
final class ProductController extends AbstractController
{
    #[Route('/{id}', name: 'app_front_product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findPublishedById($id);
        if ($product === null) {
            throw $this->createNotFoundException('Ce produit n’est pas disponible.');
        }

        return $this->render('Front/product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
