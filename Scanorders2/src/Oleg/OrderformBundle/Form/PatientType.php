<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

//use Oleg\OrderformBundle\Entity\PatientClinicalHistory;

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

//        echo "patient params=";
//        //print_r($this->params);
//        echo $this->params['type']." ".$this->params['cicle'];
//        echo "<br>";

        $flag = false;
        if( $this->params['type'] != 'single' && ($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') ) {
            //$flag = true;
        }

        $gen_attr = array('label'=>'MRN','class'=>'Oleg\OrderformBundle\Entity\PatientMrn','type'=>null);
        $builder->add('mrn', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            //'type' => new PatientMrnType($this->params),
            'read_only' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "MRN:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientmrn__',
        ));

        $gen_attr = array('label'=>'Name','class'=>'Oleg\OrderformBundle\Entity\PatientName','type'=>null);
        $builder->add('name', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            'read_only' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Name:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientname__',
        ));

        $gen_attr = array('label'=>'Age','class'=>'Oleg\OrderformBundle\Entity\PatientAge','type'=>'text');
        $builder->add('age', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            'read_only' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Age:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientage__',
        ));

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

        $gen_attr = array('label'=>'Clinical History','class'=>'Oleg\OrderformBundle\Entity\PatientClinicalHistory','type'=>null);
        $builder->add('clinicalHistory', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            //'read_only' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Clinical History:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__patientclinicalhistory__',
        ));

        if( $this->params['type'] != 'single' ) {
            $builder->add('procedure', 'collection', array(
                'type' => new ProcedureType($this->params),
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => " ",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedure__',
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
