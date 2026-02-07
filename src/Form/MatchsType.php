<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Matchs;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatchsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statut')
            ->add('dateMatch')
            ->add('dateFinMatch')
            ->add('scoreEquipe1')
            ->add('scoreEquipe2')
            ->add('equipe1', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'id',
            ])
            ->add('equipe2', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'id',
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
