<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SpecimenType extends AbstractType
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

//        echo "specimen params=";
//        //print_r($this->params);
//        echo $this->params['type']." ".$this->params['cicle'];
//        echo "<br>";

        $flag = false;
        if( $this->params['type'] != 'single' && ($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') ) {
            //$flag = true;
        }
        
        if($this->params['type'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' || $this->params['cicle'] == 'edit' ) {
            $attr = array('class' => 'ajax-combobox-procedure', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'combobox combobox-width');    //show
        }

        $builder->add('proceduretype', 'custom_selector', array(
            'label' => 'Procedure Type:',
            'required' => false,
            'attr' => $attr,
            'disabled' => $flag,
            'classtype' => 'procedureType'
        ));
            
        

        if( $this->params['type'] != 'single' ) {
            $builder->add('accession', 'collection', array(
                'type' => new AccessionType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => " ",                         
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__accession__',
            )); 
        }
        
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Specimen'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_specimentype';
    }
}
