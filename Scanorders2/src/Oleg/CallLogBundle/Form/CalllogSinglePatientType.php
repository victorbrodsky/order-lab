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

namespace Oleg\CallLogBundle\Form;

use Doctrine\ORM\EntityRepository;
//use Oleg\CallLogBundle\Form\EncounterType;
use Oleg\OrderformBundle\Form\GenericFieldType;
use Oleg\OrderformBundle\Form\PatientDobType;
use Oleg\OrderformBundle\Form\PatientSexType;
use Oleg\UserdirectoryBundle\Form\TrackerType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class CalllogSinglePatientType extends AbstractType
{

    protected $params;
    protected $entity;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

        $builder->add('id', HiddenType::class, array(
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'form-control patienttype-patient-id', 'type' => 'hidden'),
        ));

        $builder->add('mrn', CollectionType::class, array(
            'entry_type' => CalllogPatientMrnType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientmrn__',
        ));


        $builder->add('dob', CollectionType::class, array(
            'entry_type' => PatientDobType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientdob__',
        ));

        $builder->add('lastname', CollectionType::class, array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Last Name:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientlastname__',
        ));

        $builder->add('firstname', CollectionType::class, array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "First Name:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientfirstname__',
        ));

        $builder->add('middlename', CollectionType::class, array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Middle Name:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientmiddlename__',
        ));

        $builder->add('sex', CollectionType::class, array(
            'type' => new PatientSexType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientsex__',
        ));

//        $builder->add('suffix', CollectionType::class, array(
//            'type' => new PatientSexType($this->params, null),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__patientsex__',
//        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Patient',
            'form_custom_value' => null,
            'form_custom_value_entity' => null,
            //'csrf_protection' => false
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_calllogbundle_patienttype';
    }
}
