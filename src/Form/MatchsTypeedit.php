<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Matchs;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MatchsTypeedit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_match', TextType::class, [
                'label' => 'Nom du match',
                'attr' => [
                    'class' => 'team-input',
                    'placeholder' => 'Entrez le nom du match'
                ]
            ])
            // Statut fixé à "en_cours" et non modifiable
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En cours' => 'en_cours',
                    'Terminé' => 'termine',
                    'Annulé' => 'annule'
                ],
                'data' => 'en_cours',
             ])
            ->add('dateMatch', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('dateFinMatch', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            // Scores initialisés à 0
            ->add('scoreEquipe1', HiddenType::class, [
                'data' => 0
            ])
            ->add('scoreEquipe2', HiddenType::class, [
                'data' => 0
            ])
            ->add('equipe1', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nom',
            ])
            ->add('equipe2', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nom',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Matchs::class,
        ]);
    }
}
