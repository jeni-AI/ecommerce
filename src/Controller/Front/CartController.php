<?php

namespace App\Controller\Front;

use App\Entity\Client;
use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Entity\Product;
use App\Entity\RefStatutProduit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier')]
#[IsGranted('ROLE_CLIENT')]
final class CartController extends AbstractController
{
    #[Route('', name: 'app_cart', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $cart = $this->getOrCreateCart($entityManager);
        $entityManager->flush();

        return $this->render('Front/cart/index.html.twig', [
            'cart' => $cart,
            'total' => $this->getCartTotal($cart),
            'itemCount' => $this->getCartItemCount($cart),
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('cart_add_'.$product->getId(), $request->getPayload()->getString('_token'))) {
            return $this->cartErrorResponse($request, 'Le jeton de sécurité est invalide.', Response::HTTP_FORBIDDEN);
        }

        $quantity = $request->getPayload()->getInt('quantity', 1);
        if ($quantity < 1 || $product->getStock() < $quantity || !$this->isPublished($product)) {
            return $this->cartErrorResponse($request, 'Ce produit n’est plus disponible dans cette quantité.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cart = $this->getOrCreateCart($entityManager);
        $line = $this->findLine($cart, $product);
        $newQuantity = ($line?->getQuantity() ?? 0) + $quantity;
        if ($newQuantity > $product->getStock()) {
            return $this->cartErrorResponse($request, 'La quantité demandée dépasse le stock disponible.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($line === null) {
            $line = (new LignePanier())->setProduct($product);
            $cart->addLigne($line);
        }

        $line->setQuantity($newQuantity);
        $entityManager->persist($cart);
        $entityManager->flush();

        if (!$request->isXmlHttpRequest()) {
            $this->addFlash('success', 'Produit ajouté au panier.');

            return $this->redirectToRoute('app_cart', [], Response::HTTP_SEE_OTHER);
        }

        return $this->json([
            'success' => true,
            'message' => 'Produit ajouté au panier.',
            'itemCount' => $this->getCartItemCount($cart),
            'total' => $this->getCartTotal($cart),
        ]);
    }

    #[Route('/ligne/{id}/quantite', name: 'app_cart_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(
        Request $request,
        LignePanier $line,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $cart = $this->getOrCreateCart($entityManager);
        if ($line->getPanier() !== $cart) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('cart_update_'.$line->getId(), $request->getPayload()->getString('_token'))) {
            return $this->json(['success' => false, 'message' => 'Le jeton de sécurité est invalide.'], Response::HTTP_FORBIDDEN);
        }

        $quantity = $request->getPayload()->getInt('quantity');
        $product = $line->getProduct();
        if ($quantity < 1 || $product === null || $quantity > $product->getStock() || !$this->isPublished($product)) {
            return $this->json(['success' => false, 'message' => 'La quantité demandée n’est pas disponible.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $line->setQuantity($quantity);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'lineSubtotal' => $line->getSubtotal(),
            'total' => $this->getCartTotal($cart),
            'itemCount' => $this->getCartItemCount($cart),
        ]);
    }

    #[Route('/ligne/{id}/supprimer', name: 'app_cart_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(
        Request $request,
        LignePanier $line,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $cart = $this->getOrCreateCart($entityManager);
        if ($line->getPanier() !== $cart) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('cart_remove_'.$line->getId(), $request->getPayload()->getString('_token'))) {
            return $this->json(['success' => false, 'message' => 'Le jeton de sécurité est invalide.'], Response::HTTP_FORBIDDEN);
        }

        $cart->removeLigne($line);
        $entityManager->remove($line);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'total' => $this->getCartTotal($cart),
            'itemCount' => $this->getCartItemCount($cart),
            'isEmpty' => $cart->getLignes()->isEmpty(),
        ]);
    }

    private function getOrCreateCart(EntityManagerInterface $entityManager): Panier
    {
        $user = $this->getUser();
        if (!$user instanceof User || !in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            throw $this->createAccessDeniedException('Le panier est réservé aux clients.');
        }

        $client = $user->getClient();
        if ($client === null) {
            $client = (new Client())->setUser($user);
            $entityManager->persist($client);
        }

        $cart = $client->getPanier();
        if ($cart === null) {
            $cart = (new Panier())->setClient($client);
            $entityManager->persist($cart);
        }

        return $cart;
    }

    private function findLine(Panier $cart, Product $product): ?LignePanier
    {
        foreach ($cart->getLignes() as $line) {
            if ($line->getProduct() === $product) {
                return $line;
            }
        }

        return null;
    }

    private function isPublished(Product $product): bool
    {
        return $product->getCurrentStatus()?->getStatus() === RefStatutProduit::PUBLISHED;
    }

    private function getCartTotal(Panier $cart): int
    {
        $total = 0;
        foreach ($cart->getLignes() as $line) {
            $total += $line->getSubtotal();
        }

        return $total;
    }

    private function getCartItemCount(Panier $cart): int
    {
        $count = 0;
        foreach ($cart->getLignes() as $line) {
            $count += $line->getQuantity();
        }

        return $count;
    }

    private function cartErrorResponse(Request $request, string $message, int $status): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'message' => $message], $status);
        }

        $this->addFlash('danger', $message);

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
