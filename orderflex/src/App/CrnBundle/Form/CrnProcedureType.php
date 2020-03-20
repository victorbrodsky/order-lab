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

namespace App\CrnBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrnProcedureType extends AbstractType
{

    protected $params;
    protected $entity;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        if( !array_key_exists('alias', $this->params) ) {
            $this->params['alias'] = true;
        }
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

        $builder->add('id', HiddenType::class);

        $builder->add('accession', CollectionType::class, array(
            'entry_type' => CrnAccessionType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params,
                'form_custom_value_entity' => $this->entity
            ),
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'label' => false,//" ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__accession__',
        ));

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\Procedure',
            'form_custom_value' => null,
            'form_custom_value_entity' => null
            //'csrf_protection' => false
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_procedure';
    }
}
