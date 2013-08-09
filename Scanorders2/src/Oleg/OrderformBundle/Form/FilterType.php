<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {                              
        
        $search = array(
            'all' => 'All',
            'active' => 'Active',
            'completed' => 'Completed',
            'uncompleted' => 'Uncompleted',
            'cancel' => 'Cancel'
            
        );
        
        $builder->add( 'filter', 'choice', array(  
                'label' => 'Filter:',
                'max_length'=>50,
                'choices' => $search,
                'required' => true,             
                //'multiple' => true,
                //'expanded' => true,
                //'attr' => array('class' => 'horizontal_type')              
        ));                       
        
        $builder->add('search', 'text', array(
                'max_length'=>200,
                'required'=>false,
                'label'=>'Search:',            
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        //$resolver->setDefaults(array(
            //'data_class' => 'Oleg\OrderformBundle\Entity\Scan'
        //));
    }

    public function getName()
    {
        return 'filter_search_box';
    }
}
