<?php


namespace App\Form;


use App\Services\SearchSortieUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchSortieUserFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sortieQueJorganise', CheckboxType::class, [
                'label' => 'Les sorties que j\'organise',
                'required' => false,
            ])
            ->add('sortieAuquelJeParticipe', CheckboxType::class, [
                'label' => 'Les sorties auquel je participe',
                'required' => false,
            ])
            ->add('archive', CheckboxType::class, [
                'label' => 'Sorties archivées',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchSortieUser::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
