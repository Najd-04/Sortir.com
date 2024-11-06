<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'required' => false,
            ])
            ->add('dateHeureDebut', null, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('dateLimiteInscription', null, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('nbInscriptionsMax', NumberType::class, [
                'label' => 'Nombre de places',
                'required' => false,
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée',
                'required' => false,
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
                'required' => false,
            ])
            ->add('lieux', EntityType::class, [
                'label' => 'Lieu existant',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Sélectionnez un lieu existant',
                'mapped' => false,
            ])
            ->add('site', EntityType::class, [
                'label' => 'Site',
                'class' => Site::class,
                'choice_label' => 'nom',
                'required' => false,
            ])
            ->add('etat', EntityType::class, [
                'label' => 'Etat',
                'class' => Etat::class,
                'choice_label' => 'libelle',
                'required' => false,
            ])
            ->add('organisateur', EntityType::class, [
                'label' => 'Organisateur',
                'class' => Participant::class,
                'choice_label' => 'nom',
                'required' => false,
            ])
            ->add('addLieu', ButtonType::class, [
                'label' => '<i class="bi bi-plus-square"></i>',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'onclick' => 'showNewLieuForm()',
                    'style' => ''
                ],
                'label_html' => true,  // Permet de rendre l'HTML dans le label
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer la sortie',
            ]);

        // Ajouter l'événement PRE_SET_DATA pour ajouter dynamiquement le champ de lieu
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            // Vérifier une condition pour afficher le sous-formulaire lieu
            if ($event->getData() && $event->getData()->getLieu() === null) {
                // Ajouter dynamiquement le champ de type LieuType
                $form->add('lieu', LieuType::class, [
                    'label' => 'Nouveau Lieu',
                    'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
