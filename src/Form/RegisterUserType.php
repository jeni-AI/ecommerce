<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class RegisterUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => "votre adresse email",
                'attr' => [
                    'placeholder' => "indiquez votre adresse email"
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'password',
                    'hash_property_path' => 'password',
                    'attr' => [
                        'placeholder' => "indiquez votre mot de passe"
                    ]
                ],
                'second_options' => [
                    'label' => 'repeat password',
                    'attr' => [
                        'placeholder' => "indiquez à nouveau votre mot de passe"
                    ]
                ],
                'mapped' => false
            ])
            ->add('firstname', TextType::class, [
                 'label' => "votre firstname",
                 'attr' => [
                    'placeholder' => "indiquez votre firstename"
                ]
            ])
            ->add('lastname', TextType::class, [
                 'label' => "votre lastname",
                 'constraints' => [
                     new Length([
                         'min' => 2,
                         'max' => 30
                     ])
                 ],
                 'attr' => [
                     'placeholder' => "indiquez votre lastname"
                 ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => "valisez",
                'attr' => [
                    'class' => "btn btn-success"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
