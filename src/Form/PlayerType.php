<?php

namespace App\Form;

use App\Entity\Player;
use App\Enum\Niveau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Email obligatoire'),
                    new Assert\Email(message: 'Email invalide'),
                ],
            ])

            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Mot de passe obligatoire'),
                    new Assert\Length(
                        min: 8,
                        minMessage: 'Au moins {{ limit }} caractères'
                    ),
                ],
            ])

            ->add('confirmPassword', PasswordType::class, [
                'mapped' => false, // ❗ pas en BD
                'constraints' => [
                    new Assert\NotBlank(message: 'Confirmation obligatoire'),
                ],
            ])

            ->add('pays')

            ->add('niveau', ChoiceType::class, [
                'choices' => Niveau::cases(),
                'choice_label' => fn (Niveau $niveau) => $niveau->name,
            ])

            ->add('statut', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Player::class,
        ]);
    }
}
