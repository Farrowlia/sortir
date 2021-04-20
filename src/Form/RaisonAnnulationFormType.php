<?php

namespace App\Form;

use App\Services\RaisonAnnulation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaisonAnnulationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('raisonAnnulation', TextType::class, [
                'required' => true,
                'label' => 'Indiquez la raison de l\'annulation'
            ]);

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RaisonAnnulation::class,
        ]);
    }
}
