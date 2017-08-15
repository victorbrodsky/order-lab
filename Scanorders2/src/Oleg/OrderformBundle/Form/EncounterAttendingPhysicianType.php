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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EncounterAttendingPhysicianType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        if( !array_key_exists('attendingPhysicians-readonly', $this->params) ) {
            $this->params['attendingPhysicians-readonly'] = true;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('field', 'custom_selector', array(
            'label' => 'Attending Physician:',
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-encounterAttendingPhysician'),
            'required' => false,
            'read_only' => $this->params['attendingPhysicians-readonly'],
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
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encounterattendingphysiciantype';
    }
}
