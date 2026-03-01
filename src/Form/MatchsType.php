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
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
/**
 * @extends AbstractType<Matchs>
 */
class MatchsType extends AbstractType
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
            ->add('statut', HiddenType::class, [
                'data' => 'en_cours',
                'disabled' => true,
            ])
            ->add('dateMatch', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('dateFinMatch', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
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
    
    
    
        // ⭐⭐⭐ VALIDATION LOGIC (ONLY FOR CREATION FORM)
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
    
            $form = $event->getForm();
            $match = $event->getData();
    
            if (!$match) {
                return;
            }
    
            $dateMatch = $match->getDateMatch();
            $dateFin   = $match->getDateFinMatch();
    
            $now = new \DateTime();
    
            // RULE 1: match must be in the future
            if ($dateMatch && $dateMatch <= $now) {
                $form->get('dateMatch')->addError(
                    new FormError("Le match doit être programmé dans le futur")
                );
            }
    
            // RULE 2: end > start
            if ($dateMatch && $dateFin && $dateFin <= $dateMatch) {
                $form->get('dateFinMatch')->addError(
                    new FormError("La date de fin doit être après la date de début")
                );
            }
    
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Matchs::class,
        ]);
    }
}
