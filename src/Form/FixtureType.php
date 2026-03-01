<?php

namespace App\Form;

use App\Entity\Fixture;
use App\Entity\League;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matchDate')
            ->add('scoreTeam1')
            ->add('scoreTeam2')
            ->add('status')
            ->add('round')
            ->add('league', EntityType::class, [
                'class' => League::class,
                'choice_label' => 'id',
            ])
            ->add('matchLink')
         
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fixture::class,
        ]);
    }
}
