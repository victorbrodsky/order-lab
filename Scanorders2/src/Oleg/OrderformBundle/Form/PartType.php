<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class PartType extends AbstractType
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

        $flag = false;
        if( $this->params['type'] != 'single' && ($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') ) {
            //$flag = true;
        }

        $helper = new FormHelper();  
        
        if( $this->params['type'] != 'single' ) {
            $builder->add('block', 'collection', array(
                'type' => new BlockType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Block:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__block__',
            )); 
        }

        //name
        $builder->add('name', 'collection', array(
            'type' => new PartNameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Procedure Type:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partname__',
        ));

        //sourceOrgan
        $builder->add('sourceOrgan', 'collection', array(
            'type' => new PartSourceOrganType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Source Organ:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partsourceOrgan__',
        ));

        //description
        $gen_attr = array('label'=>'Gross Description','class'=>'Oleg\OrderformBundle\Entity\PartDescription','type'=>null);    //type=null => auto type
        $builder->add('description', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Gross Description:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdescription__',
        ));

        //diagnosis
        $gen_attr = array('label'=>'Diagnosis','class'=>'Oleg\OrderformBundle\Entity\PartDiagnosis','type'=>null);    //type=null => auto type
        $builder->add('diagnosis', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Diagnosis:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdiagnosis__',
        ));

        //diffDiagnoses
        $builder->add('diffDiagnoses', 'collection', array(
            'type' => new DiffDiagnosesType(),
            'disabled' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,   //"Diagnosis:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__diffDiagnoses__',
        ));

        $builder->add( 'diseaseType', 'choice', array(
            'label'=>'Type of Disease:',
            'disabled' => $flag,
            //'required'=>false,
            'choices' => array("Neoplastic"=>"Neoplastic", "Non-Neoplastic"=>"Non-Neoplastic", "None"=>"None"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type'), //'required' => '0', 'disabled'
            //'data' => 'Male',
        ));

        $builder->add( 'origin', 'choice', array(
            'label'=>'Origin:',
            'disabled' => $flag,
            //'required'=>false,
            'choices' => array("Primary"=>"Primary", "Metastatic"=>"Metastatic"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type'),
        ));

        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
            $attr = array('class' => 'ajax-combobox-organ', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'combobox combobox-width');    //show
        }
        $builder->add('primaryOrgan', 'custom_selector', array(
            'label' => 'Primary Site of Origin:',
            'disabled' => $flag,
            'attr' => $attr,
            'required' => false,
            'classtype' => 'sourceOrgan'
        ));

        //$builder->add( 'paper', new DocumentType($this->params), array('label'=>' ') );
        $builder->add('paper', 'collection', array(
            'type' => new DocumentType($this->params),
            'disabled' => $flag,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => " ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__paper__',
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Part'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_parttype';
    }
}
