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

use Oleg\OrderformBundle\Form\ArrayFieldType;
use Oleg\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EncounterAttendingPhysicianType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        if( !array_key_exists('attendingPhysicians-readonly', $this->params) ) {
            $this->params['attendingPhysicians-readonly'] = true;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $fieldAttr = array('class' => 'combobox combobox-width ajax-combobox-encounterAttendingPhysician');
        if( $this->params['attendingPhysicians-readonly'] ) {
            $fieldAttr['readonly'] = true;
        }
        $builder->add('field', ScanCustomSelectorType::class, array(
            'label' => 'Attending Physician:',
            //'attr' => array('class' => 'combobox combobox-width ajax-combobox-encounterAttendingPhysician', 'readonly' => $this->params['attendingPhysicians-readonly']),
            'attr' => $fieldAttr,
            'required' => false,
            //'disabled' => $this->params['attendingPhysicians-readonly'],
            'classtype' => 'singleUserWrapper'
            //'classtype' => 'userWrapper'
        ));

//        $builder->add('attendingPhysicianSpecialty', 'custom_selector', array(
//            'label' => 'Referring Provider Specialty:',
//            'attr' => array('class' => 'combobox combobox-width ajax-combobox-attendingPhysicianSpecialty'),
//            'required' => false,
//            'classtype' => 'attendingPhysicianSpecialty'
//        ));
//
//        $builder->add('attendingPhysicianPhone', null, array(
//            'label' => 'Referring Provider Phone Number:',
//            'attr' => array('class'=>'form-control')
//        ));
//
//        $builder->add('attendingPhysicianEmail', null, array(
//            'label' => 'Referring Provider E-Mail:',
//            'attr' => array('class'=>'form-control')
//        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterAttendingPhysician',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_encounterattendingphysiciantype';
    }
}
