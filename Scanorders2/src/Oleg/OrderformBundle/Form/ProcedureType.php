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

//        $builder->add('number', 'collection', array(
//            'type' => new ProcedureNumberType($this->params, $this->entity),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__procedurenumber__',
//        ));

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

        $builder->add('date', 'collection', array(
            'type' => new ProcedureDateType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedureencounterDate__',
        ));

        //children
        $builder->add('accession', 'collection', array(
            'type' => new AccessionType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,//" ",
            'by_reference' => false,
            'prototype' => true,
            'prototype' => true,
            'prototype_name' => '__accession__',
        ));


        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {

            //echo "flag datastructure=".$this->params['datastructure']."<br>";

            $builder->add('number', 'collection', array(
                'type' => new ProcedureNumberType($this->params, $this->entity),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedurenumber__',
            ));

            $builder->add('location', 'collection', array(
                'type' => new ProcedureLocationType($this->params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedurelocation__',
            ));

            $sources = array('WCMC Epic Ambulatory EMR','Written or oral referral');
            $params = array('name'=>'Procedure','dataClass'=>'Oleg\OrderformBundle\Entity\ProcedureOrder','typename'=>'procedureorder','sources'=>$sources);
            $builder->add('order', 'collection', array(
                'type' => new GeneralOrderType($params, null),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedureorder__',
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
