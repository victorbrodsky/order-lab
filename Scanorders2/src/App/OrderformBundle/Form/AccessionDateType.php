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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessionDateType extends AbstractType
{

    protected $params;
    protected $entity;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('field', DateType::class, array(
            'label' => "Accession Date:",
            'widget' => 'single_text',
            //'input'  => 'datetime',
            'required' => false,
            'format' => 'MM/dd/yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control accessionaccessiondate'),
        ));

        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'App\OrderformBundle\Entity\AccessionAccessionDate',
            'form_custom_value' => $this->params,
            'label' => false,
            'attr' => array('style'=>'display:none;')
        ));


        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $builder->add('time', TimeType::class, array(
                'input'  => 'datetime',
                'widget' => 'choice',
                'label'=>'Accession Time:'
            ));
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\AccessionAccessionDate',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_accessionaccessiondatetype';
    }
}
