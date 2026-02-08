<?php

namespace App\Form;

use App\Entity\League;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class LeagueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('game')
            ->add('startDate', DateType::class, [
    'widget' => 'single_text',
    'html5' => true,
])
            ->add('endDate', DateType::class, [
    'widget' => 'single_text',
    'html5' => true,
])
            ->add('numTeams')
            ->add('format')
            ->add('status')
            ->add('prizePool')
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => League::class,
        ]);
    }
}
