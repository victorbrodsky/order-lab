<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
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

        $builder->add('slide', 'collection', array(
            'type' => new SlideType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Slide:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slide__',
        ));

        //name
        $builder->add('partname', 'collection', array(
            'type' => new PartNameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Part Name:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partpartname__',
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
            'prototype_name' => '__partsourceorgan__',
        ));

        //description
        $attr = array('class'=>'textarea form-control');
        $gen_attr = array('label'=>'Gross Description','class'=>'Oleg\OrderformBundle\Entity\PartDescription','type'=>null);    //type=null => auto type
        $builder->add('description', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Gross Description:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdescription__',
        ));

        //diagnosis
        //$attr = array('class'=>'textarea form-control', 'style'=>'height:35px');
        $gen_attr = array('label'=>'Diagnosis','class'=>'Oleg\OrderformBundle\Entity\PartDisident','type'=>null);    //type=null => auto type
        $builder->add('disident', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Diagnosis:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdisident__',
        ));

        //paper
        $builder->add('paper', 'collection', array(
            'type' => new PartPaperType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partpaper__',
        ));

        //diffDiagnoses
        $gen_attr = array('label'=>'Differential Diagnoses','class'=>'Oleg\OrderformBundle\Entity\PartDiffDisident','type'=>'text');
        $attr = array('class'=>'form-control partdiffdisident-field');
        $builder->add('diffDisident', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Differential Diagnoses:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdiffdisident__',
        ));

        //diseaseType
        $gen_attr = array('label'=>'Type of Disease','class'=>'Oleg\OrderformBundle\Entity\PartDiseaseType','type'=>null);    //type=null => auto type
        $builder->add('diseaseType', 'collection', array(
            'type' => new PartDiseaseTypeType($this->params, null, $gen_attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Type of Disease:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__partdiseaseType__',
        ));



        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $params = array('labelPrefix'=>'Gross Image');
            $equipmentTypes = array('Gross Image Camera');
            $params['device.types'] = $equipmentTypes;
            $builder->add('documentContainer', new DocumentContainerType($params), array(
                'required' => false,
                'label' => false
            ));
        }

        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $builder->add('orderinfo', 'collection', array(
                'type' => new MessageType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__partmessage__',
            ));
        }

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
