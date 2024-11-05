<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
            ])
            ->add('dateHeureDebut', null, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
            ])
            ->add('dateLimiteInscription', null, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax', NumberType::class, [
                'label' => 'Nombre de places',
            ] )
            ->add('duree')
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'mapped' => false,
                'label' => 'Ville',
                'choice_label' => 'nom',
                'placeholder' => '-- Choisissez une ville--',
                'query_builder' =>  function (VilleRepository $villeRepository) {
                    return  $villeRepository->createQueryBuilder('v')
                        ->orderBy('v.nom', 'ASC');
                }
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'mapped' => false,
                'label' => 'Lieu',
                'choice_label' => 'nom',
                'placeholder' => '-- Choisissez un lieu--',
                'choices' => []
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
