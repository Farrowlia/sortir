<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la sortie'])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'html5' => true,
                'widget' => 'single_text',
                ])
            ->add('duree', IntegerType::class, ['label' => 'Durée'])
            ->add('dateCloture', DateType::class, [
                'label' => 'Date cloture inscription',
                'html5' => true,
                'widget' => 'single_text',
                ])
            ->add('nbreInscriptionMax')
            ->add('description')
//            ->add('urlImage')
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image'])
//            ->add('ville', EntityType::class, [
//                'class' => Ville::class,
//                'choice_label' => 'nom',
//                'placeholder' => 'Ville',
//                'mapped' => false
//            ])
//            ->add('lieu', EntityType::class, [
//                'class' => Lieu::class,
//                'placeholder' => 'Sélectionner un lieu',
//            ])
//            ->add('lieuForm', LieuFormType::class, [
//                'mapped' => false,
//                'label' => 'Créer un lieu',
//                'attr' => ['style' => 'display:none'],
//            ])
//            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
//            ->add('saveAndPublish', SubmitType::class, ['label' => 'Publier la sortie'])
//            ->add('cancel', ResetType::class, [
//                'label' => 'Effacer'
//            ])

        ;

//        $builder->get('lieu')->addEventListener(
//            FormEvents::POST_SUBMIT,
//            function (FormEvent $event) {
//           $form = $event->getForm();
//           dump($form->getData());
//
//        });
//            ->add('ville', EntityType::class, [
//                'class' => Ville::class,
//                'choice_label' => 'nom',
//                'placeholder' => 'Choisissez la ville',
//                'mapped' => false,
//            ])

        /*            ->add('etat', EntityType::class, [
                        'class' => Etat::class,
                        'choice_label' => 'libelle'
                    ])
                    ->add('campus', EntityType::class, [
                        'class' => Campus::class,
                        'choice_label' => 'nom'
                    ]);*/

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class
        ]);
    }

//
//    /**
//     * tentative d'afficher les détails du lieu sélectionné
//     * TODO
//     */
//    private function setLieuForm(FormInterface $form, ?Lieu $lieu)
//    {
//        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
//            'lieuForm',
//            LieuFormType::class,
//            null,
//            [
//
//            ]
//        );
//
////        $builder
////            ->add('nom', TextType::class, [
////                'label' => 'Nom *',
////                'required' => false,
////            ]) //TODO ajouter des astérisques sur les champs obligatoires
////            ->add('rue', TextType::class, [
////                'label' => 'Rue *',
////                'required' => false,
////            ])
////            ->add('ville', EntityType::class, [
////                'class' => Ville::class,
////                'placeholder' => 'Sélectionner une ville',
////                'label' => 'Ville *',
////                'required' => false,
////            ])
////            ->add('latitude')
////            ->add('longitude')
//    }

}
