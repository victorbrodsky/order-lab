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

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PartNameType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //echo "cycle=".$this->params['cycle']."<br>";

        if( $this->params['cycle'] != 'show' && $this->params['type'] == 'One-Slide Scan Order' && $this->params['cycle'] != 'amend' && $this->params['cycle'] != 'edit' ) {
            $label = false;
        } else {
            $label = 'Part ID:';
        }

        if( $this->params['cycle'] != "show" ) {
            $attr = array('class' => 'ajax-combobox ajax-combobox-partname keyfield partname-mask', 'type' => 'hidden' );
            $builder->add('field', 'custom_selector', array(
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

        $builder->add('others', new ArrayFieldType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartPartname',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartPartname',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_partnametype';
    }
}
