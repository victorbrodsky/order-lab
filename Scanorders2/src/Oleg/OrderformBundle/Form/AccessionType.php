<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccessionType extends AbstractType
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

//        $builder->add('accessionDate', 'date', array(
//            'label' => "Accession Date:",
//            'widget' => 'single_text',
//            'required' => false,
//            'format' => 'MM-dd-yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
//            'attr' => array('class' => 'datepicker form-control patientdob-mask accessiondate-field', 'style'=>'margin-top: 0;'),
//        ));
        $builder->add('accessionDate', 'collection', array(
            'type' => new AccessionDateType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__accessionaccessiondate__',
        ));

        $builder->add('accession', 'collection', array(
            'type' => new AccessionAccessionType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__accessionaccession__',
        ));

        $builder->add('part', 'collection', array(
            'type' => new PartType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Part:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__part__',
        ));


        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {

            //echo "accession flag datastructure=".$this->params['datastructure']."<br>";

            //TODO: create orderinfo type to display: order id, type, source ...
//            $builder->add('laborder', 'collection', array(
//                'type' => new LabOrderType($this->params, null),
//                'allow_add' => true,
//                'allow_delete' => true,
//                'required' => false,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__accessionlaborder__',
//            ));

//            $builder->add('outsidereport', 'collection', array(
//                'type' => new AccessionOutsidereportType($this->params, null),
//                'allow_add' => true,
//                'allow_delete' => true,
//                'required' => false,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__accessionoutsidereport__',
//            ));

        }
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Accession'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_accessiontype';
    }
}
