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

        $attr = array('class'=>'form-control form-control-modif', 'required' => 'required', 'title' => 'Example: S12-12345');

        if( $this->params['type'] == 'single') {
            $attr['style'] = 'width:100%';
        } else {
            //$attr_width = 'style' => 'width:130px';
        }


        $builder->add( 'accession', 'text', array(
                'label'=>'* Accession Number [or Label]:',
                'max_length'=>100,
                'required'=>true,
                //'attr' => array('class'=>'form-control form-control-modif','style' => 'width:130px', 'required' => 'required', 'title' => 'Example: S12-12345'),
                'attr' => $attr,
        ));      
        
//        $builder->add('date','date',array(
//            'widget' => 'single_text',
//            'format' => 'MM-dd-yyyy',
//            'attr' => array('class' => 'datepicker'),
//            'required' => false,
//            //'data' => new \DateTime(),
//            'label'=>'Accession Date:',
//        ));
        
        if( $this->params['type'] != 'single' ) {
            
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
