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
        if( $this->params['type'] != 'One-Slide Scan Order' && ($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') ) {
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
            'read_only' => $flag,
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

        $builder->add('procedure', 'collection', array(
            'type' => new ProcedureType($this->params,$this->entity),
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'label' => false,//" ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedure__',
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
