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

use App\UserdirectoryBundle\Form\AttachmentContainerType;
use App\UserdirectoryBundle\Form\DocumentContainerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\OrderformBundle\Helper\FormHelper;

class PartType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        if( !array_key_exists('show-tree-depth',$this->params) || !$this->params['show-tree-depth'] ) {
            $this->params['show-tree-depth'] = true; //show all levels
        }

        //testing
        //$this->params['datastructure'] = false;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //if X=6, show only the first 6 levels (patient + encounter + procedure + accession + part + block)
        if( $this->params['show-tree-depth'] === true || intval($this->params['show-tree-depth']) >= 6 ) {
            $builder->add('block', CollectionType::class, array(
                'entry_type' => BlockType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Block:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__block__',
            ));
        }

        //if X=7, show only the first 7 levels (patient + encounter + procedure + accession + part + block + slide)
        if( $this->params['show-tree-depth'] === true || intval($this->params['show-tree-depth']) >= 7 ) {
            $builder->add('slide', CollectionType::class, array(
                'entry_type' => SlideType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Slide:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__slide__',
            ));
        }

        //name
        $builder->add('partname', CollectionType::class, array(
            'entry_type' => PartNameType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Part ID:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partpartname__',
        ));

        //title
        $builder->add('parttitle', CollectionType::class, array(
            'entry_type' => PartTitleType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Part Title:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__parttitle__',
        ));

        //sourceOrgan
        $builder->add('sourceOrgan', CollectionType::class, array(
            'entry_type' => PartSourceOrganType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Source Organ:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partsourceorgan__',
        ));

        //description
        $attr = array('class'=>'textarea form-control');
        $gen_attr = array('label'=>'Gross Description:','class'=>'App\OrderformBundle\Entity\PartDescription','type'=>null);    //type=null => auto type
        $builder->add('description', CollectionType::class, array(
            //GenericFieldType($this->params, null, $gen_attr, $attr),
            'entry_type' => GenericFieldType::class,
            'entry_options' => array(
                'data_class' => $gen_attr['class'],
                'form_custom_value' => $this->params,
                'form_custom_value_genAttr' => $gen_attr,
                'form_custom_value_attr' => $attr
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Gross Description:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdescription__',
        ));

        //diagnosis
        //$attr = array('class'=>'textarea form-control', 'style'=>'height:35px');
        $gen_attr = array('label'=>'Diagnosis:','class'=>'App\OrderformBundle\Entity\PartDisident','type'=>null);    //type=null => auto type
        $builder->add('disident', CollectionType::class, array(
            //GenericFieldType($this->params, null, $gen_attr, $attr),
            'entry_type' => GenericFieldType::class,
            'entry_options' => array(
                'data_class' => $gen_attr['class'],
                'form_custom_value' => $this->params,
                'form_custom_value_genAttr' => $gen_attr,
                'form_custom_value_attr' => $attr
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Diagnosis:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdisident__',
        ));

        //paper
        $builder->add('paper', CollectionType::class, array(
            'entry_type' => PartPaperType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partpaper__',
        ));

        //diffDiagnoses
        $gen_attr = array('label'=>'Differential Diagnoses:','class'=>'App\OrderformBundle\Entity\PartDiffDisident','type'=>'text');
        $attr = array('class'=>'form-control partdiffdisident-field');
        $builder->add('diffDisident', CollectionType::class, array(
            //GenericFieldType($this->params, null, $gen_attr, $attr),
            'entry_type' => GenericFieldType::class,
            'entry_options' => array(
                'data_class' => $gen_attr['class'],
                'form_custom_value' => $this->params,
                'form_custom_value_genAttr' => $gen_attr,
                'form_custom_value_attr' => $attr
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Differential Diagnoses:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdiffdisident__',
        ));

        //diseaseType
        //$gen_attr = array('label'=>'Type of Disease:','class'=>'App\OrderformBundle\Entity\PartDiseaseType','type'=>null);    //type=null => auto type
        $builder->add('diseaseType', CollectionType::class, array(
            //PartDiseaseTypeType($this->params, null, $gen_attr),
            'entry_type' => PartDiseaseTypeType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Type of Disease:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdiseaseType__',
        ));



        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $params = array('labelPrefix'=>'Gross Image');
            $equipmentTypes = array('Gross Image Camera');
            $params['device.types'] = $equipmentTypes;
            $builder->add('attachmentContainer', AttachmentContainerType::class, array(
                'form_custom_value' => $params,
                'required' => false,
                'label' => false
            ));
        }

        //testing
        //$this->params['datastructure'] = false;
        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
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
                'prototype_name' => '__partmessage__',
            ));
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\Part',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_parttype';
    }
}
