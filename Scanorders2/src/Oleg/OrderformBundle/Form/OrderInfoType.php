<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\FormHelper;

class OrderInfoType extends AbstractType
{
    
    protected $multy;
    protected $service;
    
    public function __construct( $multy = false, $service = null )
    {
        $this->multy = $multy;
        $this->service = trim($service);
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $helper = new FormHelper();
                   
        $builder->add( 'type', 'hidden' ); 
        
        if( $this->multy ) {          
            $builder->add('patient', 'collection', array(
                'type' => new PatientType($this->multy),
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => " ",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patient__',
            ));                  
        }
        
        $builder->add( 'provider', 'text', array(
                'label'=>'* Ordering Provider:', 
                'max_length'=>'200', 
                'required'=>true,
                'attr' => array('required' => 'required')
        ));

        if( 0 ) {//$this->service ) {
            $builder->add( 'pathologyService', 'text', array(
                'label'=>'Pathology Service:',
                'max_length'=>'200',
                'required'=>false,
                'attr' => array('required' => 'required')
            ));
        } else {

//            $helper = new FormHelper();
//            $email = $this->get('security.context')->getToken()->getAttribute('email');
//            $service = $helper->getUserPathology($email);
//            $email = 'Gynecologic Pathology / Perinatal Pathology / Autopsy';//'oli2002@med.cornell.edu';

            $pathServices = $helper->getPathologyService();
            $pathParam = array(
                'label' => 'Pathology Service:',
                'max_length'=>200,
                'choices' => $pathServices,
                'required'=>false,
                'attr' => array('class' => 'combobox'),
            );

            $counter = 0;
            foreach( $pathServices as $ser ){
                //echo "<br>ser=(".$ser.") (".$this->service.")<br>";
                if( trim($ser) == trim($this->service) ){
                    //echo "found";
                    $key = $counter;
                    //echo " key=".$key;
                    $pathParam['data'] = $key;
                }
                $counter++;
            }

            $builder->add( 'pathologyService', 'choice', $pathParam );

        }
//

        
        $builder->add( 'priority', 'choice', array(
                'label' => '* Priority:', 
                //'max_length'=>200,
                'required' => true,
                'choices' => $helper->getPriority(),
                'data' => 'Routine',  
                'multiple' => false,
                'expanded' => true,
                'attr' => array('class' => 'horizontal_type', 'required' => 'required')
        ));
        
        $builder->add( 'slideDelivery', 
                'choice', array(  
                'label'=>'* Slide Delivery:',
                'max_length'=>200,
                'choices' => $helper->getSlideDelivery(),
                'required'=>true,
                'data' => 0,     
                'attr' => array('class' => 'combobox', 'required' => 'required')
        ));
                
        $builder->add( 'returnSlide', 
                'choice', array(
                'label'=>'* Return Slides to:', 
                'max_length'=>200,
                'choices' => $helper->getReturnSlide(),
                'required'=>true,
                'data' => 'Filing Room',
                'attr' => array('class' => 'combobox', 'required' => 'required')
        ));

        $builder->add('scandeadline','date',array(
            'widget' => 'single_text',
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker'),
            'required' => false,
            'data' => date_modify(new \DateTime(), '+2 week'),
            'label'=>'Scan Deadline:',
        ));
        
        $builder->add('returnoption', 'checkbox', array(
            'label'     => 'Return slide(s) by this date even if not scanned',
            'required'  => false,
        ));
        
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\OrderInfo'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_orderinfotype';
    }
}
