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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartNameType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //echo "cycle=".$this->params['cycle']."<br>";

        if( $this->params['cycle'] != 'show' && $this->params['type'] == 'One-Slide Scan Order' && $this->params['cycle'] != 'amend' && $this->params['cycle'] != 'edit' ) {
            $label = false;
        } else {
            $label = 'Part ID:';
        }

        if( $this->params['cycle'] != "show" ) {
            $attr = array('class' => 'ajax-combobox ajax-combobox-partname keyfield partname-mask', 'type' => 'hidden' );
            $builder->add('field', ScanCustomSelectorType::class, array(
                'label' => $label,
                'attr' => $attr,
                'required'=>false,
                'classtype' => 'partname'
            ));
        } else {
            $attr = array('class' => 'form-control keyfield partname-mask');
            $builder->add('field', null, array(
                'label' => $label,
                'attr' => $attr,
                'required'=>false,
            ));
        }

        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'App\OrderformBundle\Entity\PartPartname',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\PartPartname',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_partnametype';
    }
}
