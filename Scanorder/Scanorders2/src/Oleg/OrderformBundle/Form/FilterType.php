<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterType extends AbstractType
{

    private $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {                              

        $builder->add( 'filter', 'choice', array(  
            'label' => 'Filter by Order Status:',
            'max_length'=>50,
            'choices' => $this->params['statuses'],
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width order-status-filter')
        ));                       
        
        $builder->add('search', 'text', array(
            'max_length'=>200,
            'required'=>false,
            'label'=>'Search:',
            'attr' => array('class'=>'form-control form-control-modif limit-font-size submit-on-enter-field'),
        ));

        $builder->add('service', 'choice', array(
            'label'     => 'Services',
            'required'  => true,
            'choices' => $this->params['services'],
            'attr' => array('class' => 'combobox combobox-width')
        ));
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        //$resolver->setDefaults(array(
            //'data_class' => 'Oleg\OrderformBundle\Entity\Scan'
        //));
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'filter_search_box';
    }
}
