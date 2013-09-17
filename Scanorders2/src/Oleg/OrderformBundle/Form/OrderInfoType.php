<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class OrderInfoType extends AbstractType
{

    protected $entity;
    protected $params;
    
//    public function __construct( $type = null, $service = null, $entity = null )
    //params: type: single or clinical, educational, research
    //params: cicle: new, edit, show
    //params: service: pathology service
    //params: entity: entity itself
    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        echo "orderinfo params=";
//        print_r($this->params);
//        echo "<br>";

        $helper = new FormHelper();

//        $builder->add( 'id' );//, 'hidden' );

        $builder->add( 'type', 'hidden' ); 
        
        if( $this->params['type'] != 'single' ) {
            $builder->add('patient', 'collection', array(
                'type' => new PatientType($this->params),    //$this->type),
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => " ",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patient__',
            ));
        }

        //echo "<br>type=".$this->type."<br>";

        if( $this->params['type'] == 'educational' ) {
            //echo " add type educational ";
            $builder->add( 'educational', new EducationalType(), array('label'=>'Educational:') );
        }

        if( $this->params['type'] == 'research' ) {
            //echo " add type research ";
            $builder->add( 'research', new ResearchType(), array('label'=>'Research:') );
        }

        $builder->add( 'provider', 'text', array(
                'label'=>'* Ordering Provider:', 
                'max_length'=>'200', 
                'required'=>true,
                'attr' => array('required' => 'required', 'class'=>'form-control form-control-modif')
        ));


        //pathologyService
//        $pathServices = $helper->getPathologyService();
//        $pathParam = array(
//            'label' => 'Pathology Service:',
//            'max_length'=>200,
//            'choices' => $pathServices,
//            'required'=>false,
//            'attr' => array('class' => 'combobox combobox-width'),
//        );
//        if( $this->entity->getPathologyService() && $this->entity->getPathologyService() != "" ) { //show, edit
//            $thisname = trim( $this->entity->getPathologyService() );
//        } else {  //new
//            $thisname = trim($this->params['service']);
//        }
//        $counter = 0;
//        foreach( $pathServices as $var ){
//            if( trim( $var ) == $thisname ) {
//                $key = $counter;
//                $pathParam['data'] = $key;
//                break;
//            }
//            $counter++;
//        }
//        $builder->add( 'pathologyService', 'choice', $pathParam );
        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
            $attr = array('class' => 'ajax-combobox-pathservice', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'combobox combobox-width');    //show
        }
        $builder->add('pathologyService', 'custom_selector', array(
            'label' => 'Pathology Service:',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'pathologyService'
        ));
        
        //priority                    
        $priorityArr = $helper->getPriority();
        $priority_param = array(
            'label' => '* Priority:',
            //'max_length'=>200,
            'required' => true,
            'choices' => $priorityArr,
            'multiple' => false,
            'expanded' => true,
            'data' => 0,
            'attr' => array('class' => 'horizontal_type', 'required' => 'required'),
            'auto_initialize' => false,
        );
        $priority = $this->entity->getPriority();
        $key = 0;
        $counter = 0;
        foreach( $priorityArr as $var ){
            //echo "<br>".$var."?".$pathservice;
            if( trim( $var ) == trim( $priority ) ){
                $key = $counter;
                //$priority_param['data'] = $key;
                break;
            }
            $counter++;
        }
        $priority_param['data'] = $key;
        $builder->add( 'priority', 'choice', $priority_param);

//        $builder->add( 'priority', 'choice', array(
//                'label' => '* Priority:', 
//                //'max_length'=>200,
//                'required' => true,
//                'choices' => $helper->getPriority(),
//                'data' => 0,    //'Routine',
//                'multiple' => false,
//                'expanded' => true,
//                'attr' => array('class' => 'horizontal_type', 'required' => 'required')
//        ));
        
//        $builder->add( 'slideDelivery', 
//                'choice', array(  
//                'label'=>'* Slide Delivery:',
//                'max_length'=>200,
//                'choices' => $helper->getSlideDelivery(),
//                'required'=>true,
//                'data' => 0,     
//                'attr' => array('class' => 'combobox combobox-width', 'required' => 'required')
//        ));
        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
            $attr = array('class' => 'ajax-combobox-delivery', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'form-control form-control-modif');    //show
        }
        $builder->add('slideDelivery', 'custom_selector', array(
            'label' => '* Slide Delivery:',           
            'attr' => $attr,
            'required'=>true,
            'classtype' => 'slideDelivery'
        ));
                
//        $builder->add( 'returnSlide', 
//                'choice', array(
//                'label'=>'* Return Slides to:', 
//                'max_length'=>200,
//                'choices' => $helper->getReturnSlide(),
//                'required'=>true,
//                'data' => 0,    //'Filing Room',
//                'attr' => array('class' => 'combobox combobox-width', 'required' => 'required')
//        ));
        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
            $attr = array('class' => 'ajax-combobox-return', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'form-control form-control-modif');    //show
        }
        $builder->add('returnSlide', 'custom_selector', array(
            'label' => '* Return Slides to:',           
            'attr' => $attr,
            'required'=>true,
            'classtype' => 'returnSlide'
        ));

        $scandeadline = date_modify(new \DateTime(), '+2 week');
        if( $this->entity && $this->entity->getScandeadline() != '' ) {
            $scandeadline = $this->entity->getScandeadline();
        }
        $builder->add('scandeadline','date',array(
            'widget' => 'single_text',
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker'),
            'required' => false,
            'data' => $scandeadline,
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
