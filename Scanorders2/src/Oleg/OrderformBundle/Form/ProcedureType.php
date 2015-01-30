<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProcedureType extends AbstractType
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

        $builder->add('encounter', 'collection', array(
            'type' => new ProcedureEncounterType($this->params, $this->entity),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedureencounter__',
        ));

        $builder->add('name', 'collection', array(
            'type' => new ProcedureNameType($this->params, $this->entity),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Procedure Type:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurename__',
        ));

        $builder->add('accession', 'collection', array(
            'type' => new AccessionType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,//" ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__accession__',
        ));

        $builder->add('encounterDate', 'collection', array(
            'type' => new ProcedureEncounterDateType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedureencounterDate__',
        ));

        $builder->add('patsuffix', 'collection', array(
            'type' => new ProcedurePatsuffixType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurepatsuffix__',
        ));
        $builder->add('patlastname', 'collection', array(
            'type' => new ProcedurePatlastnameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurepatlastname__',
        ));
        $builder->add('patfirstname', 'collection', array(
            'type' => new ProcedurePatfirstnameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurepatfirstname__',
        ));
        $builder->add('patmiddlename', 'collection', array(
            'type' => new ProcedurePatmiddlenameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurepatmiddlename__',
        ));

        $builder->add('patsex', 'collection', array(
            'type' => new ProcedurePatsexType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurepatsex__',
        ));

        $attr = array('class'=>'form-control procedureage-field patientage-mask');
        $gen_attr = array('label'=>"Patient's Age (at the time of encounter)",'class'=>'Oleg\OrderformBundle\Entity\ProcedurePatage','type'=>'text');
        $builder->add('patage', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Patient's Age (at the time of encounter):",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurepatage__',
        ));

        //pathistory'
        $attr = array('class'=>'textarea form-control procedurehistory-field');
        $gen_attr = array('label'=>"Clinical History (at the time of encounter)",'class'=>'Oleg\OrderformBundle\Entity\ProcedurePathistory','type'=>null);
        $builder->add('pathistory', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Clinical History (at the time of encounter):",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurepathistory__',
        ));



        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {

            //echo "flag datastructure=".$this->params['datastructure']."<br>";

            $builder->add('location', 'collection', array(
                'type' => new ProcedureLocationType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedurelocation__',
            ));


            $builder->add('encounterorder', 'collection', array(
                'type' => new ProcedureEncounterorderType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedureencounterorder__',
            ));

            $builder->add('procedureorder', 'collection', array(
                'type' => new ProcedureProcedureorderType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedureprocedureorder__',
            ));

        }

        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Procedure'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_proceduretype';
    }
}
