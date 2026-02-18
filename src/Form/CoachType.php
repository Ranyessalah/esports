<?php

namespace App\Form;

use App\Entity\Coach;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CoachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $passwordConstraints = [
            new Assert\Length([
                'min' => 8,
                'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
            ]),
            new Assert\Regex([
                'pattern' => '/[A-Z]/',
                'message' => 'Le mot de passe doit contenir au moins une majuscule',
            ]),
            new Assert\Regex([
                'pattern' => '/[!@#$%^&*(),.?":{}|<>]/',
                'message' => 'Le mot de passe doit contenir au moins un caractère spécial',
            ]),
        ];

        if (!$isEdit) {
            array_unshift($passwordConstraints, new Assert\NotBlank([
                'message' => 'Mot de passe obligatoire',
            ]));
        }

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => ' '],
                'empty_data' => '',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => !$isEdit,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['placeholder' => ' '],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['placeholder' => ' '],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas',
                'constraints' => $passwordConstraints,
            ])
            ->add('pays', ChoiceType::class, [
                'label' => 'Pays',
                'choices' => [
                    'Tunisie' => 'Tunisie',
                    'France' => 'France',
                    'Maroc' => 'Maroc',
                    'Algérie' => 'Algérie',
                ],
                'placeholder' => 'Sélectionnez un pays',
            ])
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'choices' => [
                    'Football' => 'Football',
                    'Basketball' => 'Basketball',
                    'Tennis' => 'Tennis',
                    'Natation' => 'Natation',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'Sélectionnez une spécialité',
            ])
            ->add('disponibilite', ChoiceType::class, [
                'label' => 'Disponible ?',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coach::class,
            'is_edit' => false,
        ]);
    }
}
