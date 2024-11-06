<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileEditFormType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('pseudo', TextType::class, [
        'label' => 'Pseudo',
        'required' => true,
      ])
      ->add('prenom', TextType::class, [
        'label' => 'Prénom',
        'required' => true,
      ])
      ->add('nom', TextType::class, [
        'label' => 'Nom',
        'required' => true,
      ])
      ->add('telephone', TelType::class, [
        'label' => 'Téléphone',
        'required' => false,
      ])
      ->add('email', EmailType::class, [
        'label' => 'Email',
        'required' => true,
      ])
      ->add('photoProfil', FileType::class, [
        'label' => 'Photo de profil',
        'mapped' => false,
        'required' => false,
        'constraints' => [
          new File([
            'maxSize' => '2M',
            'mimeTypes' => ['image/jpeg', 'image/png'],
            'mimeTypesMessage' => 'Veuillez télécharger une image au format JPG ou PNG',
          ]),
        ],
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Participant::class,
    ]);
  }
}
