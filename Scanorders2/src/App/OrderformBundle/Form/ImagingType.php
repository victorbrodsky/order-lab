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

namespace App\OrderformBundle\Form;

use App\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use App\UserdirectoryBundle\Form\DocumentContainerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

use App\OrderformBundle\Helper\FormHelper;

class ImagingType extends AbstractType
{
      
    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //echo "ImagingType <br>";

        //scanregion
        $attr = array('class' => 'ajax-combobox-scanregion', 'type' => 'hidden');
        $options = array(
            'label' => 'Region to scan:',
            //'max_length'=>500,
            'attr' => $attr,
            'classtype' => 'scanRegion'
        );
        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') {
            $options['data'] = 'Entire Slide';
        }
        $builder->add('scanregion', ScanCustomSelectorType::class, $options);

        //note
        $builder->add('note', TextareaType::class, array(
                //'max_length'=>5000,
                'required'=>false,
                'label'=>'Reason for Scan/Note:',
                'attr' => array('class'=>'textarea form-control'),   //form-control
        ));

        ///////////// mag /////////////
        $tooltip =  "Scanning at 40X magnification is done Friday to Monday. ".
            "Some of the slides (about 7% of the batch) may have to be rescanned the following week in order to obtain sufficient image quality. ".
            "We will do our best to expedite the process.";

        $builder->add('magnification', EntityType::class, array(
            'class' => 'AppOrderformBundle:Magnification',
            'choice_label' => 'name',
            'label'=>'Magnification:',
            'required'=> true,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width element-with-select2-tooltip-always', 'title'=>$tooltip, 'data-toggle'=>'tooltip'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));

        if( array_key_exists('datastructure', $this->params) &&  $this->params['datastructure'] == true ) {

            $builder->add('imageId', TextType::class, array(
                'required'=>false,
                'label'=>'Image ID:',
                'attr' => array('class'=>'form-control'),
            ));

            $builder->add('source', null, array(
                'required'=>false,
                'label'=>'Image ID Source System:',
                'attr' => array('class' => 'combobox combobox-width'),
            ));

            //Image container
            $params = array('labelPrefix'=>'Attached Image');
            $equipmentTypes = array('Whole Slide Scanners','Microscope Camera');
            $params['device.types'] = $equipmentTypes;
            $params['document.imageId'] = false;
            $params['document.source'] = false;
            $params['document.device.label'] = 'Image Acquisition Device:';
            $params['document.datetime.label'] = 'Image Acquisition Date:';
            $params['document.time.label'] = 'Image Acquisition Time:';
            $params['document.provider.label'] = 'Image Acquired By:';
            $params['document.link.label'] = 'Image Link:';
            $builder->add('documentContainer', DocumentContainerType::class, array(
                'data_class' => 'App\UserdirectoryBundle\Entity\DocumentContainer',
                'form_custom_value' => $params,
                'label' => false
            ));

        }



        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            //echo "slide datastructure <br>";

            $builder->add('message', CollectionType::class, array(
                'entry_type' => MessageObjectType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                    'form_custom_value_entity' => null
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__imagingmessage__',
            ));

        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\Imaging',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_imagingtype';
    }


}
