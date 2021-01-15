<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\ResAppBundle\Form;

use App\UserdirectoryBundle\Form\DocumentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResAppUploadType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if(0) {
            $builder->add('file', FileType::class, array(
                'label' => 'CSV file:',
                'required' => false,
                'mapped' => false,
                //'attr' => array('class'=>'form-control')
            ));
        }

        $builder->add('erasFiles', CollectionType::class, array(
            //'type' => new DocumentType($this->params),
            'entry_type' => DocumentType::class,
            'label' => 'ERAS application files:',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('datalocker',HiddenType::class, array(
            "mapped" => false,
            'attr' => array('class' => 'resapp-datalocker-field')
        ));

        $builder->add('upload', SubmitType::class, array(
            'label' => "Upload and Extract Data",
            //'disabled' => true,
//            'attr' => array('class' => 'btn btn-default', "onClick=disableUploadBtn();")
            'attr' => array('class' => 'btn btn-default')
        ));

//        $builder->add('addbtn', SubmitType::class, array(
//            'label' => "Add Listed Applications",
//            'attr' => array('class' => 'btn btn-primary')
//        ));
        $builder->add('addbtn', SubmitType::class, array(
            'label' => 'Add Listed Applications',
            'attr' => array('class'=>'btn btn-primary resapp-addbtn', 'onclick'=>'return resappValidateRequest(true);')
        ));
        //Additional add button without validation. Used by JS to add the listed application in handsontable by confirmation modal
        $builder->add('addbtnforce', SubmitType::class, array(
            'label' => 'Add Listed Applications',
            'attr' => array('class'=>'btn btn-primary resapp-addbtnforce', 'type' => 'hidden')
        ));
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\ResAppBundle\Entity\InputDataFile',
            'csrf_protection' => true,
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_resappbundle_bulkupload';
    }
}
