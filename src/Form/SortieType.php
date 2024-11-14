<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class SortieType extends AbstractType
{
    public function __construct(private EntityManagerInterface $entityManager){}

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
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => function ($ville) {
                    return $ville->getNom().' '.$ville->getCodePostal();
                },
                'label' => 'Ville',
                'required' => false,
                'mapped' => false,
                'placeholder' => '--Sélectionnez une ville existante--',
            ])
            ->add('lieux', EntityType::class, [
                'label' => 'Lieu existant',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => '--Sélectionnez un lieu existant--',
                'mapped' => false,
                'choices' => [] // Initialement vide
            ])
            ->add('addLieu', ButtonType::class, [
                'label' => '<i class="bi bi-plus-square"></i>',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'onclick' => 'showNewLieuForm()',
                    'style' => ''
                ],
                'label_html' => true,  // Permet de rendre l'HTML dans le label
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // Ajout du champ 'lieu' si la condition est remplie
            if ($data && $data->getLieu() === null) {
                $form->add('lieu', LieuType::class, [
                    'label' => 'Nouveau Lieu',
                    'label_attr' => [
                        'style' => 'display:none;'  // Masque le label en CSS
                    ],
                    'required' => false,
                ]);
            }

            // Ajout du champ 'lieux' si la ville est définie
            if ($data && $data->getVille()) {
                $ville = $data->getVille();
                $lieux = $this->entityManager->getRepository(Lieu::class)->findBy(['ville' => $ville]);

                $form->add('lieux', EntityType::class, [
                    'class' => Lieu::class,
                    'choices' => $lieux,
                    'choice_label' => 'nom',
                    'label' => 'Lieu',
                    'required' => false,
                    'placeholder' => '--Sélectionnez d\'abord une ville--',
                    'mapped' => false
                ]);
            }
        });


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['ville']) && $data['ville']) {
                $ville = $this->entityManager->getRepository(Ville::class)->find($data['ville']);
                $lieux = $this->entityManager->getRepository(Lieu::class)->findBy(['ville' => $ville]);

                $form->add('lieux', EntityType::class, [
                    'class' => Lieu::class,
                    'choices' => $lieux,
                    'choice_label' => 'nom',
                    'label' => 'Lieu',
                    'required' => false,
                    'placeholder' => '--Sélectionnez d\'abord une ville--',
                    'mapped' => false
                ]);
            }

            if (empty($data['lieux'])) {
                $form->get('lieu')->add('nom', TextType::class, [
                    'constraints' => [
                        new NotBlank(['message' => "Le nom du nouveau lieu est requis si un lieu existant n'est pas sélectionné"]),
                    ],
                ]);

                $form->get('lieu')->add('rue', TextType::class, [
                    'constraints' => [
                        new NotBlank(['message' => "La rue du nouveau lieu est requis si un lieu existant n'est pas sélectionné"]),
                    ],
                ]);
                $form->get('lieu')->add('latitude', NumberType::class, [
                    'constraints' => [
                        new NotBlank(['message' => "La latitude du nouveau lieu est requis si un lieu existant n'est pas sélectionné"]),
                    ],
                ]);
                $form->get('lieu')->add('longitude', NumberType::class, [
                    'constraints' => [
                        new NotBlank(['message' => "La longitude du nouveau lieu est requis si un lieu existant n'est pas sélectionné"]),
                    ],
                ]);
                $form->get('lieu')->add('ville', EntityType::class, [
                    'class' => Ville::class,
                    'choice_label' => function ($ville) {
                        return $ville->getNom() . ' ' . $ville->getCodePostal();
                    },
                    'required' => false,
                    'placeholder' => "--Sélectionnez une ville existante--",
                    'constraints' => [
                        new NotBlank(['message' => "La ville du nouveau lieu est requis si un lieu existant n'est pas sélectionné"]),
                    ],
                ]);

            }
        });

        // Ajout d'un écouteur d'événements pour valider la logique personnalisée
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            // Vérifie si la sélection est vide et si les champs du sous-formulaire ne sont pas valides
            if (empty($data->lieux) && !$form->get('lieu')->isValid()) {
                // Ajoute une erreur personnalisée à l'input de type select
                $form->get('lieux')->addError(new FormError("Veuillez sélectionner un lieu existant ou corriger les champs requis d'un nouveau lieu"));
            }
        });
    }

    private function updateLieuxOptions($form, $ville): void
    {
        // Si une ville est choisie, mettre à jour les lieux associés
        if ($ville) {
            $lieux = $this->entityManager->getRepository(Lieu::class)->findBy(['ville' => $ville]);

            // Mettre à jour le champ 'lieu' avec les options dynamiques
            $form->add('lieux', EntityType::class, [
                'class' => Lieu::class,
                'choices' => $lieux,
                'choice_label' => 'nom',
                'label' => 'Lieux',
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
