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
            $required = true;
            $gen_attr = array('label'=>'Accession Number [or Label]:','class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        } else {
            $required = false;
            $gen_attr = array('label'=>'Accession Number [or Label]:','class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
            //$attr_width = 'style' => 'width:130px';
        }


//        $builder->add( 'accession', 'text', array(
//                'label'=>'* Accession Number [or Label]:',
//                'max_length'=>100,
//                'required'=>$required,
//                //'attr' => array('class'=>'form-control form-control-modif','style' => 'width:130px', 'required' => 'required', 'title' => 'Example: S12-12345'),
//                'attr' => $attr,
//        ));

        $builder->add('accession', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Accession Number [or Label]:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__accessionaccession__',
        ));

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
