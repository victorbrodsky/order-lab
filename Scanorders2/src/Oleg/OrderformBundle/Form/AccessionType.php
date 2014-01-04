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

        $attr = array(  
            'class'=>'form-control form-control-modif keyfield accession-mask', 
            'title' => 'Example: S12-123456 or SS12-123456. Valid Accession#: A00-1 through ZZ99-999999', 
            //'data-inputmask'=>"'mask': 'A[A]99-9[999999]'"
       );

        if( $this->params['type'] == 'One Slide Scan Order') {
            $attr['style'] = 'width:100%';
            $gen_attr = array('label'=>false,'class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        } else {
            $gen_attr = array('label'=>'Accession Number [or Label]','class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        }

        $builder->add('accession', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr, $attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Accession Number [or Label]:",
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
