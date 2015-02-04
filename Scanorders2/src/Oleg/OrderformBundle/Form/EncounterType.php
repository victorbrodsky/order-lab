<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EncounterType extends AbstractType
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

        //children
        $builder->add('procedure', 'collection', array(
            'type' => new ProcedureType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedure__',
        ));




        $builder->add('number', 'collection', array(
            'type' => new EncounterNumberType($this->params, $this->entity),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounternumber__',
        ));
//
//        $builder->add('name', 'collection', array(
//            'type' => new EncounterNameType($this->params, $this->entity),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => "Encounter Type:",
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__encountername__',
//        ));

        $builder->add('date', 'collection', array(
            'type' => new EncounterDateType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterdate__',
        ));

        $builder->add('patsuffix', 'collection', array(
            'type' => new EncounterPatsuffixType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatsuffix__',
        ));
        $builder->add('patlastname', 'collection', array(
            'type' => new EncounterPatlastnameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatlastname__',
        ));
        $builder->add('patfirstname', 'collection', array(
            'type' => new EncounterPatfirstnameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatfirstname__',
        ));
        $builder->add('patmiddlename', 'collection', array(
            'type' => new EncounterPatmiddlenameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatmiddlename__',
        ));

        $builder->add('patsex', 'collection', array(
            'type' => new EncounterPatsexType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatsex__',
        ));

        $attr = array('class'=>'form-control encounterage-field patientage-mask');
        $gen_attr = array('label'=>"Patient's Age (at the time of encounter)",'class'=>'Oleg\OrderformBundle\Entity\EncounterPatage','type'=>'text');
        $builder->add('patage', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Patient's Age (at the time of encounter):",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpatage__',
        ));

        //pathistory'
        $attr = array('class'=>'textarea form-control encounterhistory-field');
        $gen_attr = array('label'=>"Clinical History (at the time of encounter)",'class'=>'Oleg\OrderformBundle\Entity\EncounterPathistory','type'=>null);
        $builder->add('pathistory', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Clinical History (at the time of encounter):",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__encounterpathistory__',
        ));



        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {

            //echo "flag datastructure=".$this->params['datastructure']."<br>";

            $builder->add('location', 'collection', array(
                'type' => new EncounterLocationType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterlocation__',
            ));

            $sources = array('WCMC Epic Ambulatory EMR','Written or oral referral');
            $params = array('name'=>'Encounter','dataClass'=>'Oleg\OrderformBundle\Entity\EncounterOrder','typename'=>'encounterorder','sources'=>$sources);
            $builder->add('order', 'collection', array(
                'type' => new GeneralOrderType($params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterorder__',
            ));

            $builder->add('inpatientinfo', 'collection', array(
                'type' => new EncounterInpatientinfoType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__encounterinpatientinfo__',
            ));

        }

        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Encounter'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encountertype';
    }
}
