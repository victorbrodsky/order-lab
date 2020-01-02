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

use Oleg\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Doctrine\ORM\EntityRepository;

class SpecialStainsType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //field
        $builder->add('field', TextareaType::class, array(
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
        $builder->add('staintype', ScanCustomSelectorType::class, $options );

        //stainothers
        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockSpecialStains',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockSpecialStains',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_specialstainstype';
    }
}
