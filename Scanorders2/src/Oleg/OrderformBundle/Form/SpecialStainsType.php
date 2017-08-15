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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Doctrine\ORM\EntityRepository;

class SpecialStainsType extends AbstractType
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

        //field
        $builder->add('field', 'textarea', array(
            'label' => 'Associated Special Stain Result:',
            'required' => false,
            'attr' => array('class'=>'textarea form-control form-control-modif')
        ));

        //staintype
        $attr = array('class' => 'ajax-combobox-staintype', 'type' => 'hidden');
        $options = array(
            'label' => 'Associated Special Stain Name:',
            'required' => false,
            'attr' => $attr,
            'classtype' => 'staintype'
        );
        //do not default "H&E" in Associated Stains
        //if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') {
        //    $options['data'] = 1;
        //}
        $builder->add('staintype', 'custom_selector', $options );

        //stainothers
        $builder->add('others', new ArrayFieldType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockSpecialStains',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockSpecialStains'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_specialstainstype';
    }
}
