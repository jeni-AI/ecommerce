<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Ex. : T-shirt en coton bio'],
            ])
            ->add('description', null, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 5, 'placeholder' => 'Décrivez les caractéristiques du produit…'],
            ])
            ->add('price', null, [
                'label' => 'Prix (FCFA)',
                'attr' => ['placeholder' => 'Ex. : 25'],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                'attr' => ['min' => 0],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'label',
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
                'query_builder' => static fn (EntityRepository $repository) => $repository
                    ->createQueryBuilder('category')
                    ->orderBy('category.label', 'ASC'),
            ])
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
                'choice_label' => 'label',
                'label' => 'Marque',
                'placeholder' => 'Choisir une marque',
                'query_builder' => static fn (EntityRepository $repository) => $repository
                    ->createQueryBuilder('brand')
                    ->orderBy('brand.label', 'ASC'),
            ])
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image du produit',
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Choisissez une image JPG, PNG ou WebP.',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
