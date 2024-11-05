<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Code Postal']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ville']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'btn btn-info']
            ])


            /*

            ->add('nom')
            ->add('codePostal')
            ->add ('submit', SubmitType::class, [
                'label' => 'Ajouter'])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
