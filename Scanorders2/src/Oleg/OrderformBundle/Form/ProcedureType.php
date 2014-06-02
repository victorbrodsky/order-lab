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

        //simple fields

        $builder->add('encounterDate', 'date', array(
            'label' => "Encounter Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control patientdob-mask proceduredate-field', 'style'=>'margin-top: 0;'),
        ));

        $builder->add( 'patname', 'text', array(
            'label'=>"Patient's Name (at the time of encounter):",
            'required'=>false,
            'attr' => array('class' => 'form-control procedurename-field')
        ));

        $builder->add( 'patsex', 'choice', array(
            'label'=>"Patient's Sex (at the time of encounter):",
            'choices' => array("Female"=>"Female", "Male"=>"Male", "Unspecified"=>"Unspecified"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type proceduresex-field')
        ));

        $builder->add( 'patage', 'text', array(
            'label'=>"Patient's Age (at the time of encounter):",
            'required'=>false,
            'attr' => array('class' => 'form-control procedureage-field patientage-mask')
        ));

        $builder->add('pathistory', 'textarea', array(
            'max_length'=>10000,
            'required'=>false,
            'label'=>'Clinical History (at the time of encounter):',
            'attr' => array('class'=>'textarea form-control procedurehistory-field'),
        ));
        
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
