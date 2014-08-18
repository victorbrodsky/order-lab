<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oleg\UserdirectoryBundle\Util\UserUtil;

class FilterType extends AbstractType
{

    protected $statuses;
    protected $user;
    protected $services;

    public function __construct( $statuses = null, $user = null, $services = null )
    {
        $this->statuses = $statuses;
        $this->user = $user;
        $this->services = $services;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {                              

        $builder->add( 'filter', 'choice', array(  
                'label' => 'Filter by Order Status:',
                'max_length'=>50,
                //'choices' =>$this->statuses,  // $this->statuses->name, //$search,
                'choices' => $this->statuses,
                'required' => true,
                //'multiple' => true,
                //'expanded' => true,
                'attr' => array('class' => 'combobox combobox-width order-status-filter')
        ));                       
        
        $builder->add('search', 'text', array(
                'max_length'=>200,
                'required'=>false,
                'label'=>'Search:',
                'attr' => array('class'=>'form-control form-control-modif'),
        ));

        
        if( $this->services ) {
            
            $userUtil = new UserUtil();

            $builder->add('service', 'choice', array(
                'label'     => 'Services',
                'required'  => true,
                'choices' => $this->services,
                'attr' => array('class' => 'combobox combobox-width')
            ));
            
        } else {
            
            $userUtil = new UserUtil();

            $builder->add('service', 'choice', array(
                'label'     => 'Services',
                'required'  => true,
                'choices' => $userUtil->generateUserPathServices($this->user),
                'attr' => array('class' => 'combobox combobox-width')
            ));
            
        }
        
        
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
