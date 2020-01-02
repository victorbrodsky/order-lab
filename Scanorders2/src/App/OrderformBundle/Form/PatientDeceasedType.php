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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PatientDeceasedType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('deceased', CheckboxType::class, array(
            'label'     => 'Deceased:',
            'required'  => false,
        ));

        $builder->add('deathdate',DateTimeType::class,array(
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'label'=>'Date of Death:',
        ));
//        $builder->add('deathdate', 'date', array(
//            'input'  => 'datetime',
//            'widget' => 'single_text',
//            'label'=>'Date of Death:',
//            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
//        ));

        $builder->add('deathtime', TimeType::class, array(
            'input'  => 'datetime',
            'widget' => 'choice',
            'label'=>'Time of Death:'
        ));

        //other fields from abstract
        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientDeceased',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientDeceased',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_patientdeceasedtype';
    }
}
