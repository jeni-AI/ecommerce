<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ]);

        $passwordOptions = [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'password',
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
            ];

        if (!$options['edit_mode']) {
            $passwordOptions['first_options']['hash_property_path'] = 'password';
        } else {
            $passwordOptions['required'] = false;
        }

        $builder
            ->add('password', RepeatedType::class, $passwordOptions)
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
            ]);

        if ($options['allow_role_selection']) {
            $builder->add('role', EntityType::class, [
                'class' => Role::class,
                'label' => 'Rôle',
                'choice_label' => 'label',
                'placeholder' => 'Sélectionner un rôle',
                'query_builder' => static fn (EntityRepository $repository) => $repository
                    ->createQueryBuilder('role')
                    ->andWhere('role.code IN (:codes)')
                    ->setParameter('codes', ['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_CLIENT'])
                    ->orderBy('role.code', 'ASC'),
                'attr' => [
                    'class' => 'form-control pb-2 espace'
                ],
            ]);
        }

        $builder->add('submit', SubmitType::class, [
                 'label' => "valisez",
                 'attr' => [
                     'class' => "btn btn-success"
                 ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'edit_mode' => false,
            'allow_role_selection' => false,
        ]);

        $resolver->setAllowedTypes('edit_mode', 'bool');
        $resolver->setAllowedTypes('allow_role_selection', 'bool');
    }
}
