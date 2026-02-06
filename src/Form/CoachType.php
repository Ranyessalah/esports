<?php

namespace App\Form;

use App\Entity\Coach;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class CoachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'id' => 'email',
                    'placeholder' => ' '
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Email obligatoire']),
                    new Email(['message' => 'Email invalide'])
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => [
                    'id' => 'password',
                    'placeholder' => ' '
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Mot de passe obligatoire']),
                    new Length(['min' => 8, 'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères'])
                ]
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirm Password',
                'mapped' => false,
                'attr' => [
                    'id' => 'confirm-password',
                    'placeholder' => ' '
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez confirmer votre mot de passe'])
                ]
            ])
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'choices' => [
                    'League of Legends' => 'League of Legends',
                    'Valorant' => 'Valorant',
                    'Counter-Strike 2' => 'Counter-Strike 2',
                    'Dota 2' => 'Dota 2',
                    'Fortnite' => 'Fortnite',
                    'Rocket League' => 'Rocket League',
                    'Overwatch 2' => 'Overwatch 2',
                    'FIFA/FC' => 'FIFA/FC',
                ],
                'placeholder' => 'Choisir une spécialité',
                'attr' => [
                    'placeholder' => ' '
                ]
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'attr' => [
                    'placeholder' => ' '
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Pays obligatoire'])
                ]
            ])
            ->add('disponibilite', CheckboxType::class, [
                'label' => 'Je suis disponible pour du coaching',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coach::class,
        ]);
    }
}