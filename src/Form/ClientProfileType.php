<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ClientProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'constraints' => [new Length(max: 30)],
            ])
            ->add('personalAddress', TextType::class, [
                'label' => 'Adresse personnelle',
                'required' => false,
                'constraints' => [new Length(max: 255)],
            ])
            ->add('personalAddressComplement', TextType::class, [
                'label' => 'Complément d’adresse',
                'required' => false,
                'constraints' => [new Length(max: 255)],
            ])
            ->add('personalCity', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'constraints' => [new Length(max: 120)],
            ])
            ->add('personalPostalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
                'constraints' => [new Length(max: 20)],
            ])
            ->add('personalCountry', TextType::class, [
                'label' => 'Pays',
                'required' => false,
                'constraints' => [new Length(max: 120)],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
