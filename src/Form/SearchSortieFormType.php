<?php


namespace App\Form;


use App\Entity\Campus;
use App\Services\SearchSortie;
use App\Services\SearchSortieUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;

class SearchSortieFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher par mots-clés'
                ]
            ])
            ->add('campus', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => 'Campus'
            ])
            ->add('archive', CheckboxType::class, [
                'label' => 'Sortie terminée',
                'required' => false,
            ])
            ->add('dateMin', DateType::class, [
                'label' => 'Entre le ',
                'required' => false,
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('dateMax', DateType::class, [
                'label' => 'et le ',
                'required' => false,
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('reset', ResetType::class, [
                'label' => 'Effacer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchSortie::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
