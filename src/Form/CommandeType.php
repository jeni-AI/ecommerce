<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code de commande',
            ])
            ->add('client', EntityType::class, [
                'class' => User::class,
                'choice_label' => static fn (User $user): string => sprintf(
                    '%s %s (%s)',
                    $user->getFirstname(),
                    $user->getLastname(),
                    $user->getEmail()
                ),
                'label' => 'Client',
                'placeholder' => 'Sélectionner un client',
            ])
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
            ])
            ->add('totalAmount', MoneyType::class, [
                'label' => 'Montant total',
                'currency' => 'XOF',
                'divisor' => 1,
            ])
            ->add('paymentStatus', ChoiceType::class, [
                'label' => 'Statut du paiement',
                'choices' => [
                    'Non payé' => Commande::PAYMENT_PENDING,
                    'Payé' => Commande::PAYMENT_PAID,
                    'Remboursé' => Commande::PAYMENT_REFUNDED,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
