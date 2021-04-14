<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
            ->add('nom', TextType::class, ['label' => 'titre de la sortie'])
            ->add('dateDebut', DateType::class, ['html5' => true, 'widget' => 'single_text',])
            ->add('duree')
            ->add('dateCloture', DateType::class, ['html5' => true, 'widget' => 'single_text',])
            ->add('nbreInscriptionMax')
            ->add('description')
            ->add('urlImage')
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez la ville',
                'mapped' => false,
            ])
/*            ->add('lieu', EntityType::class,             [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'auto_initialize' => false,
                'placeholder' => 'La ville doit être sélectionnée',
                'choices' => [],

            ])*/
/*            ->add('lieuForm', LieuFormType::class, [
                ''
            ])*/
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle'
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom'
            ]);
          $builder->get('ville')->addEventListener(
          FormEvents::POST_SUBMIT,
          function (FormEvent $event) {
              /*$ville = $event->getForm()->getData();*/
              $form = $event->getForm();
              $this->addLieuField($form->getParent(), $form->getData());

              /* pour info $form->getParent() == récupère le formulaire initial */
              /*vidéo youtube 35:15*/

          }
        );

    }

    /**
     * Rajoute au formulaire un champ Lieux en fonction de la ville sélectionnée
     */
    private function addLieuField(FormInterface $form, Ville $ville)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'lieu',
            EntityType::class,
            null,
            [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'auto_initialize' => false,
                'placeholder' => $ville ? 'Sélectionner un lieu existant' : 'La ville doit être sélectionnée',
                'choices' => $ville ? $ville->getLieux() : [],

            ]
        );
        /* après c'est plutôt une méthode à utiliser dans modifier la sortie*/
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event)
            {
                $data = $event->getData();
                /* @var $lieu Lieu */ //ce commentaire permet d'obtenir l'autocomplétion
                $lieu = $data->getLieu();
                //TODO ecouter si la ville est sélectionnée
                if ($lieu) {
                    $ville = $lieu->getVille();
                    $form = $event->getForm();
                    $this->addLieuField($form, $ville);
                    $form->get('ville')->setData($ville);
                } else {
                    $this->addLieuField($form, $ville);
                }
            }
        );
        $form->add($builder->getForm());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
