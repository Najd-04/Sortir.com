<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $submitLabel = $options['submit_label'];

        $builder
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Code Postal'],
                'required' => true,
            ])
            ->add('nom', TextType::class, [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ville'],
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' =>$submitLabel,
                'attr' => ['class' => 'btn btn-info']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
            'submit_label' => 'Submit',
        ]);
    }
}
