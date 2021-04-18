<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])

            ->add('dateDebut', DateTimeType::class, [
                'date_label' => 'Choisir une date de début',
                'time_label' => 'Choisir une heure de début',
                'html5' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
//                'date_format' => 'dd/MM/yyyy'
//                'widget' => 'choice',
                ])

            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
                'attr' => ['min' => 1, 'max' => 1000],
                'invalid_message' => 'le nombre doit être positif et entier'
                ])

            ->add('dateCloture', DateTimeType::class, [
                'date_label' => 'Choisir une date de fin',
                'time_label' => 'Choisir une heure de fin',
                'html5' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                ])

            ->add('nbreInscriptionMax', IntegerType::class, [
                'attr' => ['min' => 1, 'max' => 100],
                'invalid_message' => 'le nombre doit être positif et entier'

                ])

            ->add('description')

            ->add('urlImage', UrlType::class, [
                'invalid_message' => "url incorrect",
                'trim' => true,
            ])

            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'placeholder' => 'Sélectionner un lieu',

            ])
//            ->add('lieuForm', LieuFormType::class, [
//                'mapped' => false,
//                'label' => 'Créer un lieu',
//                'attr' => ['style' => 'display:none'],
//            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('saveAndPublish', SubmitType::class, ['label' => 'Publier la sortie'])
            ->add('cancel', ResetType::class, [
                'label' => 'Annuler'
            ])

        ;
/* Cette méthode n'est pas nécessaire si l'input des dates est en html,
 le choix de la date est automatiquement positionné à now */
//        $builder->get('dateDebut')->addModelTransformer(new CallbackTransformer(
//            function ($value) {
//                if (!$value) {
//                    return new \DateTime('now +1 month');
//                }
//                return $value;
//            },
//            function ($value) {
//                return $value;
//            }
//        ));

        $builder->get('lieu')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
           $form = $event->getForm();
           dump($form->getData());

        });
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

    /**
     * tentative d'afficher les détails du lieu sélectionné
     * TODO
     */
    private function setLieuForm(FormInterface $form, ?Lieu $lieu)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'lieuForm',
            LieuFormType::class,
            null,
            [

            ]
        );

//        $builder
//            ->add('nom', TextType::class, [
//                'label' => 'Nom *',
//                'required' => false,
//            ]) //TODO ajouter des astérisques sur les champs obligatoires
//            ->add('rue', TextType::class, [
//                'label' => 'Rue *',
//                'required' => false,
//            ])
//            ->add('ville', EntityType::class, [
//                'class' => Ville::class,
//                'placeholder' => 'Sélectionner une ville',
//                'label' => 'Ville *',
//                'required' => false,
//            ])
//            ->add('latitude')
//            ->add('longitude')
    }
}
