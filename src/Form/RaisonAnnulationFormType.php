<?php

namespace App\Form;

use App\Services\RaisonAnnulation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaisonAnnulationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('raisonAnnulation', TextareaType::class, [
                'required' => true,
                'label' => false,
                'attr' => ['class' => 'col-8 rounded', 'rows' => '4'],
            ]);

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RaisonAnnulation::class,
        ]);
    }
}
