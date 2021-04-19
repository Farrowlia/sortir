<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'required' => true,
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-4 col-form-label'],
                'attr' => ['class' => 'col-6'],
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'html5' => true,
                'widget' => 'single_text',
                'required' => true,
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-4 col-form-label'],
                'attr' => ['class' => 'col-3'],
            ])
            ->add('dateCloture', DateType::class, [
                'label' => 'Date de clôture des inscriptions',
                'html5' => true,
                'widget' => 'single_text',
                'required' => true,
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-4 col-form-label'],
                'attr' => ['class' => 'col-2'],

            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'required' => true,
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-4 col-form-label'],
                'attr' => ['class' => 'col-1'],
            ])
            ->add('nbreInscriptionMax', null, [
                'label' => "Max de participants",
                'required' => true,
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-4 col-form-label'],
                'attr' => ['class' => 'col-1'],

            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-4 col-form-label'],
                'attr' => ['class' => 'mb-1 col-10',
                    'rows' => '4'],
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image',
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-2 col-form-label'],
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'placeholder' => 'Choisir une ville',
                'mapped' => false,
                'required' => true,
                'auto_initialize' => false,
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-2 col-form-label'],
                'attr' => ['class' => 'col-5'],
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'placeholder' => "Choisissez d'abord une ville",
                'mapped' => true,
                'required' => true,
                'row_attr' => ['class' => 'form-group-row'],
                'label_attr' => ['class' => 'col-2 col-form-label'],
                'attr' => ['class' => 'col-8'],
            ]);


        // permet d'afficher la ville dans le formulaire de modification d'une sortie
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            if ($event->getData()->getLieu() !== null) {
                $ville = $event->getData()->getLieu()->getVille();
                $form = $event->getForm();
                if ($ville) {
                    $form->get('ville')->setData($ville);
                    dump('SortieFormType_eventListener' . $ville);

                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class
        ]);
    }

//        $builder
//            ->get('ville')->addEventListener(
//                FormEvents::POST_SET_DATA,
//                function (FormEvent $event) {
//                    $form = $event->getForm();
////                    dd($form->getParent()->get('ville'));
//                    $form->getParent()->add('lieu', EntityType::class, [
//                        'class' => Lieu::class,
//                        'choice_label' => 'nom',
//                        'placeholder' => 'Choisir un lieu',
//                        'mapped' => false,
//                        'required' => false,
//                        'choices' => $form->getData()->getLieux()
//                    ]);
//                }
//            );

    //            ->add('lieuForm', LieuFormType::class, [
//                'mapped' => false,
//                'label' => 'Créer un lieu',
//                'attr' => ['style' => 'display:none'],
//            ]);
//            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
//            ->add('saveAndPublish', SubmitType::class, ['label' => 'Publier la sortie'])
//            ->add('cancel', ResetType::class, [
//                'label' => 'Effacer'
//            ])


//    private function addLieuField(FormInterface $form, ?Lieu $lieu)
//    {
//        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
//            'departement',
//            EntityType::class,
//            null,
//            [
//                'class'           => 'AppBundle\Entity\Departement',
//                'placeholder'     => $region ? 'Sélectionnez votre département' : 'Sélectionnez votre région',
//                'mapped'          => false,
//                'required'        => false,
//                'auto_initialize' => false,
//                'choices'         => $region ? $region->getDepartements() : []
//            ]
//        );
//        $builder->addEventListener(
//            FormEvents::POST_SUBMIT,
//            function (FormEvent $event) {
//                $form = $event->getForm();
//                $this->addVilleField($form->getParent(), $form->getData());
//            }
//        );
//        $form->add($builder->getForm());
//    }

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
