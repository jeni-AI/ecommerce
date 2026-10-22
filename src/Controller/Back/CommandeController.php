<?php

namespace App\Controller\Back;

use App\Entity\Commande;
use App\Entity\RefStatutCmd;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/commandes')]
final class CommandeController extends AbstractController
{
    #[Route('', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('back/commande/index.html.twig', [
            'commandes' => $commandeRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}/statut', name: 'app_commande_status_update', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        Commande $commande,
        EntityManagerInterface $entityManager
    ): Response {
        $field = $request->getPayload()->getString('field');
        $tokenId = 'update-commande-'.$field.$commande->getId();
        if (!$this->isCsrfTokenValid($tokenId, $request->getPayload()->getString('_token'))) {
            return $this->json(['message' => 'Le jeton de sécurité est invalide.'], Response::HTTP_FORBIDDEN);
        }

        if ($field === 'status') {
            $labels = [
                RefStatutCmd::PENDING => 'En attente',
                RefStatutCmd::VALIDATED => 'Validée',
                RefStatutCmd::REJECTED => 'Non validée',
            ];
            $status = $commande->getCurrentStatus();
            if ($status === null) {
                $status = (new RefStatutCmd())->setCommande($commande);
                $commande->setStatusRecord($status);
            }
            $nextValues = [
                RefStatutCmd::PENDING => RefStatutCmd::VALIDATED,
                RefStatutCmd::VALIDATED => RefStatutCmd::REJECTED,
                RefStatutCmd::REJECTED => RefStatutCmd::PENDING,
            ];
            $value = $nextValues[$status->getStatus()] ?? RefStatutCmd::PENDING;
            $status->setStatus($value)->setCreatedAt(new \DateTimeImmutable());
            $label = $labels[$value];
        } elseif ($field === 'paymentStatus') {
            $labels = [
                Commande::PAYMENT_PENDING => 'Non payé',
                Commande::PAYMENT_PAID => 'Payé',
                Commande::PAYMENT_REFUNDED => 'Remboursé',
            ];
            $nextValues = [
                Commande::PAYMENT_PENDING => Commande::PAYMENT_PAID,
                Commande::PAYMENT_PAID => Commande::PAYMENT_REFUNDED,
                Commande::PAYMENT_REFUNDED => Commande::PAYMENT_PENDING,
            ];
            $value = $nextValues[$commande->getPaymentStatus()] ?? Commande::PAYMENT_PENDING;
            $commande->setPaymentStatus($value);
            $label = $labels[$value];
        } else {
            return $this->json(['message' => 'Le type de statut est invalide.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json([
            'status' => $value,
            'label' => $label,
            'message' => $field === 'paymentStatus'
                ? 'Le statut du paiement a été mis à jour.'
                : 'Le statut de la commande a été mis à jour.',
        ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande->setStatusRecord(
                (new RefStatutCmd())
                    ->setCommande($commande)
                    ->setStatus(RefStatutCmd::PENDING)
            );
            $entityManager->persist($commande);
            $entityManager->flush();
            $this->addFlash('success', 'La commande a été créée.');

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/commande/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('back/commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Commande $commande,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La commande a été modifiée.');

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Commande $commande,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('delete'.$commande->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Le jeton de sécurité est invalide.');
        }

        $entityManager->remove($commande);
        $entityManager->flush();
        $this->addFlash('success', 'La commande a été supprimée.');

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

}
