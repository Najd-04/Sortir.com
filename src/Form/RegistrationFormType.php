<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('pseudo', TextType::class, [
'label' => 'Pseudo',
'required' => true,
'constraints' => [
new NotBlank(['message' => 'Veuillez entrer un pseudo']),
new Length(['max' => 30]),
],
])
->add('prenom', TextType::class, [
'label' => 'Prénom',
'required' => true,
'constraints' => [
new NotBlank(['message' => 'Veuillez entrer votre prénom']),
new Length(['max' => 30]),
],
])
->add('nom', TextType::class, [
'label' => 'Nom',
'required' => true,
'constraints' => [
new NotBlank(['message' => 'Veuillez entrer votre nom']),
new Length(['max' => 30]),
],
])
->add('telephone', TelType::class, [
'label' => 'Téléphone',
'required' => false,
'constraints' => [
new Length(['max' => 10]),
],
])
->add('email', EmailType::class, [
'label' => 'Email',
'required' => true,
'constraints' => [
new NotBlank(['message' => 'Veuillez entrer votre email']),
new Length(['max' => 180]),
],
])
->add('plainPassword', RepeatedType::class, [
'type' => PasswordType::class,
'first_options' => [
'label' => 'Mot de passe',
'constraints' => [
new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères']),
],
],
'second_options' => [
'label' => 'Confirmez le mot de passe',
],
'invalid_message' => 'Les mots de passe doivent correspondre.',
'mapped' => false, // Ne pas mapper ce champ à l'entité
'required' => false, // Rendre ce champ facultatif lors de l'édition
])
->add('site', EntityType::class, [
'class' => Site::class,
'label' => 'Ville d\'attachement',
'choice_label' => 'nom',
'expanded' => false,
'multiple' => false,
'placeholder' => 'Sélectionnez une ville',
'constraints' => [
new NotBlank(['message' => 'Veuillez sélectionner une ville']),
],
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
