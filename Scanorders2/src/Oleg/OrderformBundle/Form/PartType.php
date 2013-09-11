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
        $builder->add('name', 'choice', array(
            'choices' => $helper->getPart(),
            'required' => true,
            'label' => 'Part Name:',
            'max_length' => '3',
            'attr' => array('class' => 'combobox', 'style' => 'width:70px', 'required' => 'required', 'disabled')
        ));

//        $builder->add('name', 'choice', array(
//            'choices' => $helper->getPart(),
//            'required'=>true,
//            'label'=>'Part Name:',
//            'max_length'=>'3',
//            //'data' => 0,
////            'attr' => array('style' => 'width:70px'),
//            'attr' => array('class' => 'combobox', 'style' => 'width:70px', 'required' => 'required', 'disabled')
//        ));
        
//        $builder->add( 'sourceOrgan', 'text', array(
//                'label'=>'Source Organ:', 
//                'max_length'=>'100', 
//                'required'=>false
//        ));
//        $builder->add( 'sourceOrgan',
//                'choice', array(
//                'label'=>'Source Organ:',
//                'max_length'=>'100',
//                'choices' => $helper->getSourceOrgan(),
//                'required'=>false,
//                'attr' => array('class' => 'combobox combobox-width'), // 'style' => 'width:345px'),
////                'attr' => array('class' => 'combobox', 'style' => 'width:70px', 'required' => 'required', 'disabled')
//                //'data' => 0,
//        ));
        $builder->add('sourceOrgan', null, array(
            'label' => 'Source Organ:',
            'attr' => array('class' => 'combobox combobox-width')
        ));
        
        $builder->add( 'description', 'textarea', array(
                'label'=>'Gross Description:',
                'max_length'=>'10000', 
                'required'=>false,
                'attr' => array('class'=>'form-control'),
        ));
        
        $builder->add( 'diagnosis', 'textarea', array(
                'label'=>'Diagnosis:',
                'max_length'=>'10000', 
                'required'=>false,
                'attr' => array('class'=>'form-control'),
        ));
        
//        $builder->add( 'diffDiagnosis', 'textarea', array(
//                'label'=>'Differential Diagnoses:',
//                'max_length'=>'10000',
//                'required'=>false,
//                'attr' => array('class'=>'form-control'),
//        ));
        $builder->add('diffDiagnoses', 'collection', array(
            'type' => new DiffDiagnosesType(),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Diagnosis:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__diffDiagnoses__',
        ));

//        $builder->add( 'diseaseType', 'text', array(
//                'label'=>'Disease Type:',
//                'max_length'=>'100',
//                'required'=>false,
//                'attr' => array('class'=>'form-control form-control-modif'),
//        ));
        $builder->add( 'diseaseType', 'choice', array(
            'label'=>'Type of Disease:',
            'required'=>false,
            'choices' => array("Neoplastic"=>"Neoplastic", "Non-Neoplastic"=>"Non-Neoplastic"), //, "None"=>"None"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type'), //'required' => '0', 'disabled'
            //'data' => 'Male',
        ));

        $builder->add( 'origin', 'choice', array(
            'label'=>'Origin:',
            'required'=>false,
            'choices' => array("Primary"=>"Primary", "Metastatic"=>"Metastatic"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type'),
        ));

        $builder->add('primaryOrgan', null, array(
            'label' => 'Primary Site of Origin:',
            'attr' => array('class' => 'combobox combobox-width')
        ));

//        $builder->add( 'accession', new AccessionType(), array(
//            'label'=>' ',
//            'required'=>false,
//            //'hidden'=>true,
//        ));

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
