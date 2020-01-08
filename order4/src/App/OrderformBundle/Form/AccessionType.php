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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessionType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        if( !array_key_exists('show-tree-depth',$this->params) || !$this->params['show-tree-depth'] ) {
            $this->params['show-tree-depth'] = true; //show all levels
        }
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $readonly = false;
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure-patient' ) {
            $readonly = true;
        }

        $builder->add('accessionDate', CollectionType::class, array(
            'entry_type' => AccessionDateType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__accessionaccessiondate__',
        ));

        $builder->add('accession', CollectionType::class, array(
            'entry_type' => AccessionAccessionType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
            ),
            //'disabled' => $readonly,
            'attr' => array('readonly'=>$readonly),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__accessionaccession__',
        ));

        //if X=5, show only the first 5 levels (patient + encounter + procedure + accession + part)
        if( $this->params['show-tree-depth'] === true || intval($this->params['show-tree-depth']) >= 5 ) {
            $builder->add('part', CollectionType::class, array(
                'entry_type' => PartType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Part:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__part__',
            ));
        }


        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            //echo "accession flag datastructure=".$this->params['datastructure']."<br>";
            $params = array('labelPrefix'=>'Autopsy Image');
            $equipmentTypes = array('Autopsy Camera');
            $params['device.types'] = $equipmentTypes;
            $builder->add('attachmentContainer', AttachmentContainerType::class, array(
                'form_custom_value' => $params,
                'required' => false,
                'label' => false
            ));
        }

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
                'prototype_name' => '__accessionmessage__',
            ));
        }
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\Accession',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_accessiontype';
    }
}
