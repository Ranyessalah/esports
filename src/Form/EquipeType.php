<?php

namespace App\Form;
use App\Entity\User;
use App\Entity\Coach;
use App\Entity\Player;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Equipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('logoFile', FileType::class, [
    'label' => 'Logo de l’équipe',
    'mapped' => false,
    'required' => false,   
])

            ->add('game')
            ->add('categorie')
           ->add('coach', EntityType::class, [
    'class' => User::class,
    'choice_label' => 'email',
    'query_builder' => function (EntityRepository $er) {
        return $er->createQueryBuilder('u')
            ->where('u INSTANCE OF :coach')
            ->setParameter('coach', Coach::class);
    },
])

            ->add('joueurs', EntityType::class, [
    'class' => User::class,
    'choice_label' => 'email',
    'multiple' => true,
    'query_builder' => function (EntityRepository $er) {
        return $er->createQueryBuilder('u')
            ->where('u INSTANCE OF :player')
            ->setParameter('player', Player::class);
    },
])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
