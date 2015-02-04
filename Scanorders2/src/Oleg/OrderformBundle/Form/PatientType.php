<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
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

        $flag = false;
        if( $this->params['type'] != 'One-Slide Scan Order' && ($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') ) {
            //$flag = true;
        }

        $builder->add('mrn', 'collection', array(
            'type' => new PatientMrnType($this->params, null),
            'read_only' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientmrn__',
        ));

        $builder->add('dob', 'collection', array(
            'type' => new PatientDobType($this->params, null),
            //'read_only' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
//            'label' => "Dob:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientdob__',
        ));

        $attr = array('class'=>'textarea form-control patient-clinicalhistory-field');
        $gen_attr = array('label'=>'Clinical Summary','class'=>'Oleg\OrderformBundle\Entity\PatientClinicalHistory','type'=>null);
        $builder->add('clinicalHistory', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            //'read_only' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Clinical Summary:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientclinicalhistory__',
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

        //use these fields only for data reviewer and for view
        if( 0 ) {

            $attr = array('class'=>'form-control patientname-field', 'disabled' => 'disabled');
            $gen_attr = array('label'=>'Name','class'=>'Oleg\OrderformBundle\Entity\PatientName','type'=>null);
            $builder->add('lastname', 'collection', array(
                'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
                'read_only' => $flag,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Name:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientlastname__',
            ));

//            $attr = array('class'=>'form-control patientage-field patientage-mask', 'disabled' => 'disabled');
//            $gen_attr = array('label'=>'Age','class'=>'Oleg\OrderformBundle\Entity\PatientAge','type'=>'text');
//            $builder->add('age', 'collection', array(
//                'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
//                'read_only' => $flag,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'required' => false,
//                'label' => "Age:",
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__patientage__',
//            ));

            $builder->add('sex', 'collection', array(
                'type' => new PatientSexType($this->params, null),
                'read_only' => $flag,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientsex__',
            ));

        }


        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {

            //echo "flag datastructure=".$this->params['datastructure']."<br>";

            $builder->add('race', 'collection', array(
                'type' => new PatientRaceType($this->params, null),
                'read_only' => $flag,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientrace__',
            ));

            $builder->add('deceased', 'collection', array(
                'type' => new PatientDeceasedType($this->params, null),
                'read_only' => $flag,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientdeceased__',
            ));

            $builder->add('contactinfo', 'collection', array(
                'type' => new PatientContactinfoType($this->params, null),
                'read_only' => $flag,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patientcontactinfo__',
            ));

        }


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Patient'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_patienttype';
    }
}
