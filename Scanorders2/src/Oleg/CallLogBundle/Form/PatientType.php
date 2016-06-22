<?php

namespace Oleg\CallLogBundle\Form;

use Oleg\OrderformBundle\Form\EncounterType;
use Oleg\OrderformBundle\Form\GenericFieldType;
use Oleg\OrderformBundle\Form\PatientDobType;
use Oleg\OrderformBundle\Form\PatientSexType;
use Oleg\UserdirectoryBundle\Form\TrackerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class PatientType extends AbstractType
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

        //echo "patient: type=".$this->params['type']."<br>";

        $builder->add('mrn', 'collection', array(
            'type' => new PatientMrnType($this->params, null),
            //'allow_add' => true,
            //'allow_delete' => true,
            'required' => false,
            //'by_reference' => false,
            //'prototype' => true,
            //'prototype_name' => '__patientmrn__',
        ));


        $builder->add('dob', 'collection', array(
            'type' => new PatientDobType($this->params, null),
            //'read_only' => $flag,
            //'allow_add' => true,
            //'allow_delete' => true,
            'required' => false,
//            'label' => "Dob:",
            //'by_reference' => false,
            //'prototype' => true,
            //'prototype_name' => '__patientdob__',
        ));

        $builder->add('encounter', 'collection', array(
            'type' => new EncounterType($this->params,$this->entity),
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'label' => false,//" ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounter__',
        ));

//        $builder->add('lastname', 'collection', array(
//            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => "Last Name:",
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__patientlastname__',
//        ));
//
//        $builder->add('firstname', 'collection', array(
//            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => "First Name:",
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__patientfirstname__',
//        ));
//
//        $builder->add('middlename', 'collection', array(
//            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => "Middle Name:",
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__patientmiddlename__',
//        ));
//
//        $builder->add('sex', 'collection', array(
//            'type' => new PatientSexType($this->params, null),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__patientsex__',
//        ));
//
//        $builder->add('suffix', 'collection', array(
//            'type' => new PatientSexType($this->params, null),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__patientsex__',
//        ));



    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Patient'
        ));
    }

    public function getName()
    {
        return 'oleg_calllogbundle_patienttype';
    }
}
