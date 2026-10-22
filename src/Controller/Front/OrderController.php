<?php

namespace App\Controller\Front;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Entity\RefStatutCmd;
use App\Entity\RefStatutProduit;
use App\Entity\User;
use App\Form\CheckoutType;
use App\Repository\CommandeRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande')]
#[IsGranted('ROLE_CLIENT')]
final class OrderController extends AbstractController
{
    #[Route('/mes-commandes', name: 'app_order_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('Front/order/index.html.twig', [
            'orders' => $commandeRepository->findForClient($this->getClientUser()),
        ]);
    }

    #[Route('/valider', name: 'app_order_checkout', methods: ['GET', 'POST'])]
    public function checkout(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getClientUser();
        $client = $this->getOrCreateClient($user, $entityManager);
        $cart = $client->getPanier();

        if ($cart === null || $cart->getLignes()->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide.');

            return $this->redirectToRoute('app_cart', [], Response::HTTP_SEE_OTHER);
        }

        $entityManager->flush();
        $form = $this->createForm(CheckoutType::class, null, ['client' => $client]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $connection = $entityManager->getConnection();
            $connection->beginTransaction();

            try {
                foreach ($cart->getLignes() as $line) {
                    $product = $line->getProduct();
                    if ($product === null) {
                        continue;
                    }

                    $entityManager->lock($product, LockMode::PESSIMISTIC_WRITE);
                    if (
                        $product->getStock() < $line->getQuantity()
                        || $product->getCurrentStatus()?->getStatus() !== RefStatutProduit::PUBLISHED
                    ) {
                        $connection->rollBack();
                        $form->addError(new FormError(sprintf(
                            'Le produit « %s » n’est plus disponible dans la quantité demandée.',
                            $product->getName()
                        )));

                        return $this->renderCheckout($form, $cart);
                    }
                }

                $client
                    ->setTelephone($form->get('deliveryPhone')->getData())
                    ->setDeliveryAddress($form->get('deliveryAddress')->getData())
                    ->setDeliveryAddressComplement($form->get('deliveryAddressComplement')->getData())
                    ->setDeliveryCity($form->get('deliveryCity')->getData())
                    ->setDeliveryPostalCode($form->get('deliveryPostalCode')->getData())
                    ->setDeliveryCountry($form->get('deliveryCountry')->getData());

                $order = (new Commande())
                    ->setClient($user)
                    ->setPaymentMethod($form->get('paymentMethod')->getData())
                    ->setDeliveryPhone($form->get('deliveryPhone')->getData())
                    ->setDeliveryAddress($form->get('deliveryAddress')->getData())
                    ->setDeliveryAddressComplement($form->get('deliveryAddressComplement')->getData())
                    ->setDeliveryCity($form->get('deliveryCity')->getData())
                    ->setDeliveryPostalCode($form->get('deliveryPostalCode')->getData())
                    ->setDeliveryCountry($form->get('deliveryCountry')->getData());

                $total = 0;
                foreach ($cart->getLignes()->toArray() as $cartLine) {
                    $product = $cartLine->getProduct();
                    if ($product === null) {
                        continue;
                    }

                    $orderLine = (new LigneCommande())
                        ->setProduct($product)
                        ->setQuantity($cartLine->getQuantity())
                        ->setUnitPrice($product->getPrice());
                    $order->addLigne($orderLine);
                    $total += $orderLine->getSubtotal();
                    $product->setStock($product->getStock() - $cartLine->getQuantity());
                    $cart->removeLigne($cartLine);
                    $entityManager->remove($cartLine);
                }

                $order->setTotalAmount($total);
                $order->setStatusRecord(
                    (new RefStatutCmd())
                        ->setCommande($order)
                        ->setStatus(RefStatutCmd::PENDING)
                );
                $entityManager->persist($order);
                $entityManager->flush();
                $connection->commit();
            } catch (\Throwable $exception) {
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }

                throw $exception;
            }

            $this->addFlash('success', 'Votre commande a été enregistrée.');

            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderCheckout($form, $cart);
    }

    #[Route('/{id}', name: 'app_order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Commande $order): Response
    {
        if ($order->getClient() !== $this->getClientUser()) {
            throw $this->createNotFoundException();
        }

        return $this->render('Front/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    private function renderCheckout(\Symfony\Component\Form\FormInterface $form, Panier $cart): Response
    {
        $total = 0;
        foreach ($cart->getLignes() as $line) {
            $total += $line->getSubtotal();
        }

        return $this->render('Front/order/checkout.html.twig', [
            'form' => $form,
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    private function getClientUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User || !in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            throw $this->createAccessDeniedException('La commande est réservée aux clients.');
        }

        return $user;
    }

    private function getOrCreateClient(User $user, EntityManagerInterface $entityManager): Client
    {
        $client = $user->getClient();
        if ($client === null) {
            $client = (new Client())->setUser($user);
            $entityManager->persist($client);
        }

        if ($client->getPanier() === null) {
            $entityManager->persist((new Panier())->setClient($client));
        }

        return $client;
    }
}
