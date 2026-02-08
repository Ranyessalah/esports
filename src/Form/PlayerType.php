<?php

namespace App\Form;

use App\Entity\Player;
use App\Enum\Niveau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
           ->add('plainPassword', PasswordType::class, [
    'label' => 'Mot de passe',
    'mapped' => false,
    'required' => true,
    'attr' => ['placeholder' => ' '],
    'constraints' => [
        new Assert\NotBlank([
            'message' => 'Mot de passe obligatoire'
        ]),
        new Assert\Length([
            'min' => 8,
            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères'
        ]),
        new Assert\Regex([
            'pattern' => '/[A-Z].*[!@#$%^&*(),.?":{}|<>]/',
            'message' => 'Le mot de passe doit commencer par une majuscule et contenir au moins un caractère spécial',
        ]),
    ],
])
            ->add('confirmPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Confirmer le mot de passe',
                'required' => true,
            ])
            ->add('pays', ChoiceType::class, [
                'choices' => [
                    'Tunisie' => 'Tunisie',
                    'France' => 'France',
                    'Maroc' => 'Maroc',
                    'Algérie' => 'Algérie',
                ],
            ])
            ->add('niveau', ChoiceType::class, [
                'choices' => Niveau::cases(),
                'choice_label' => fn ($niveau) => $niveau->name,
            ])
            ->add('statut', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Player::class]);
    }
}
