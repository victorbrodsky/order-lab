<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterType extends AbstractType
{

    protected $statuses;

    public function __construct( $statuses = null )
    {
        $this->statuses = $statuses;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {                              

        $builder->add( 'filter', 'choice', array(  
                'label' => 'Filter by Order Status:',
                'max_length'=>50,
                //'choices' =>$this->statuses,  // $this->statuses->name, //$search,
                'choices' => $this->statuses,
                'required' => false,
                //'multiple' => true,
                //'expanded' => true,
                'attr' => array('class' => 'combobox combobox-width', 'style'=>'width:60%')
        ));                       
        
        $builder->add('search', 'text', array(
                'max_length'=>200,
                'required'=>false,
                'label'=>'Search:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'width:60%'),
        ));

        $builder->add('service', 'checkbox', array(
            'label'     => 'My service',
            'required'  => false,
//            'attr' => array('type' => 'checkbox')
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
