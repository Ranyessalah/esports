<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Matchs;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class MatchsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Planifié' => 'planifie',
                    'Annulé' => 'annule',
                    'En cours' => 'en_cours',
                    'Terminé' => 'termine',
                ],
                'placeholder' => '— Sélectionner le statut —',
                'attr' => [
                    'class' => 'form-select form-select-lg'
                ]
            ])
           ->add('dateMatch', DateTimeType::class, [
    'widget' => 'single_text',
    'html5' => true,
])

->add('dateFinMatch', DateTimeType::class, [
    'widget' => 'single_text',
    'html5' => true,
])

->add('scoreEquipe1', HiddenType::class)
->add('scoreEquipe2', HiddenType::class)
            ->add('equipe1', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nom',
            ])
            ->add('equipe2', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Matchs::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}