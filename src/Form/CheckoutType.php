<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $client = $options['client'];

        $builder
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Moyen de paiement',
                'choices' => [
                    'Airtel Money' => Commande::METHOD_AIRTEL_MONEY,
                    'Moov Money' => Commande::METHOD_MOOV_MONEY,
                    'Virement bancaire' => Commande::METHOD_BANK_TRANSFER,
                ],
                'constraints' => [new NotBlank()],
            ])
            ->add('deliveryPhone', TelType::class, [
                'label' => 'Téléphone du destinataire',
                'data' => $client->getTelephone(),
                'constraints' => [new NotBlank(message: 'Le téléphone de livraison est obligatoire.')],
            ])
            ->add('deliveryAddress', TextType::class, [
                'label' => 'Adresse de livraison',
                'data' => $client->getDeliveryAddress(),
                'constraints' => [new NotBlank(message: 'L’adresse de livraison est obligatoire.')],
            ])
            ->add('deliveryAddressComplement', TextType::class, [
                'label' => 'Complément d’adresse',
                'required' => false,
                'data' => $client->getDeliveryAddressComplement(),
            ])
            ->add('deliveryCity', TextType::class, [
                'label' => 'Ville',
                'data' => $client->getDeliveryCity(),
                'constraints' => [new NotBlank(message: 'La ville est obligatoire.')],
            ])
            ->add('deliveryPostalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
                'data' => $client->getDeliveryPostalCode(),
            ])
            ->add('deliveryCountry', TextType::class, [
                'label' => 'Pays',
                'data' => $client->getDeliveryCountry(),
                'constraints' => [new NotBlank(message: 'Le pays est obligatoire.')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
        $resolver->setRequired('client');

        $resolver->setAllowedTypes('client', Client::class);
    }
}
