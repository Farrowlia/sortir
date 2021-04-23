<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom *',
                'required' => true,
                'row_attr' => ['class' => 'col-sm-11 form-group-row'],
                'label_attr' => ['class' => 'col-sm-3 col-form-label'],
                'attr' => ['class' => 'm-2 col-sm-8'],
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue *',
                'required' => true,
                'row_attr' => ['class' => 'col-sm-11 form-group-row'],
                'label_attr' => ['class' => 'col-sm-3 col-form-label'],
                'attr' => ['class' => 'm-2 col-sm-8'],
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'placeholder' => 'Sélectionner une ville',
                'label' => 'Ville *',
                'required' => true,
                'row_attr' => ['class' => 'col-sm-11 form-group-row'],
                'label_attr' => ['class' => 'col-sm-3 col-form-label'],
                'attr' => ['class' => 'm-2 col-sm-8'],
            ])
            ->add('latitude', NumberType::class, [
                'required' => false,
                'row_attr' => ['class' => 'col-sm-11 form-group-row'],
                'label_attr' => ['class' => 'col-sm-3 col-form-label'],
                'attr' => ['class' => 'm-2 col-sm-5'],
            ])
            ->add('longitude', NumberType::class, [
                'required' => false,
                'row_attr' => ['class' => 'col-sm-11 form-group-row'],
                'label_attr' => ['class' => 'col-sm-3 col-form-label'],
                'attr' => ['class' => 'm-2 col-sm-5'],
                
            ])
//            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
//            ->add('reset', ResetType::class, [
//                'label' => 'Annuler'
//            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
/*            'empty_data' => function (FormInterface $form) {
                return new Lieu($form->get('lieu')->getData());
            }*/
        ]);
    }
}
