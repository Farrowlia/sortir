<?php

namespace App\Form;

use App\Entity\Ville;
use App\Services\FileCsvUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class FileCsvUploadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'multiple' => false,
                'required' => true,
                'label' => false,
            ]);

//        $builder
//            ->add('file', FileType::class, [
//                'label' => false,
//                'mapped' => false,
//                'required' => true,
//                'constraints' => [
//                    new File([
//                        'mimeTypes' => [
//                            'text/x-comma-separated-values',
//                            'text/comma-separated-values',
//                            'text/x-csv',
//                            'text/csv',
//                            'text/plain',
//                            'application/octet-stream',
//                            'application/vnd.ms-excel',
//                            'application/x-csv',
//                            'application/csv',
//                            'application/excel',
//                            'application/vnd.msexcel',
//                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
//                        ],
//                        'mimeTypesMessage' => "This document isn't valid.",
//                    ])
//                ],
//            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FileCsvUpload::class,
        ]);
    }
}
