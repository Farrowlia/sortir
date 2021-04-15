<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
                'required' => false,
            ]) //TODO ajouter des astérisques sur les champs obligatoires
            ->add('rue', TextType::class, [
                'label' => 'Rue *',
                'required' => false,
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionner une ville',
                'label' => 'Ville *',
                'required' => false,
            ])
            ->add('latitude')
            ->add('longitude')

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
