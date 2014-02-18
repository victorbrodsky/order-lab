<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;
use Doctrine\ORM\EntityRepository;

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
//        echo "type=".$this->params['type']."<br>";
//        echo "<br>";

        $helper = new FormHelper();

        $builder->add( 'oid' , 'hidden', array('attr'=>array('class'=>'orderinfo-id')) );

        //$builder->add( 'type', 'hidden' );
        $builder->add('type', 'entity', array(
            'class' => 'OlegOrderformBundle:FormType',
            'required' => true,
            'attr' => array('type'=>'hidden'),
        ));

        //add children
        if( $this->params['type'] != 'Multi-Slide Table' ) {
            $builder->add('patient', 'collection', array(
                'type' => new PatientType($this->params,$this->entity),    //$this->type),
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

        if( $this->params['type'] == 'Educational Multi-Slide Scan Order' || $this->params['type'] == 'Multi-Slide Table' || $this->params['type'] == 'One Slide Scan Order' ) {
            $builder->add( 'educational', new EducationalType(), array('label'=>'Educational:') );
        }

        if( $this->params['type'] == 'Research Multi-Slide Scan Order' || $this->params['type'] == 'Multi-Slide Table' || $this->params['type'] == 'One Slide Scan Order' ) {
            $builder->add( 'research', new ResearchType(), array('label'=>'Research:') );
        }

        $attr = array('class' => 'ajax-combobox-pathservice', 'type' => 'hidden');
        $builder->add('pathologyService', 'custom_selector', array(
            'label' => 'Pathology Service:',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'pathologyService'
        ));

        //priority
        $priorityArr = array(
            'label' => '* Priority:',
            'choices' => $helper->getPriority(),
            'required' => true,
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type', 'required'=>'required')
        );
        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
            $priorityArr['data'] = 'Routine';    //new
        }
        $builder->add( 'priority', 'choice', $priorityArr);

        //slideDelivery
        $attr = array('class' => 'ajax-combobox-delivery', 'type' => 'hidden');
        $builder->add('slideDelivery', 'custom_selector', array(
            'label' => '* Slide Delivery:',           
            'attr' => $attr,
            'required'=>true,
            'classtype' => 'slideDelivery'
        ));

        $attr = array('class' => 'ajax-combobox-return', 'type' => 'hidden');
        $builder->add('returnSlide', 'custom_selector', array(
            'label' => '* Return Slides to:',           
            'attr' => $attr,
            'required'=>true,
            'classtype' => 'returnSlide'
        ));

        //scandeadline
        if( $this->params['cicle'] == 'new' ) {
            $scandeadline = date_modify(new \DateTime(), '+2 week');
        } else {
            $scandeadline = null;
        }

        if( $this->entity && $this->entity->getScandeadline() != '' ) {
            $scandeadline = $this->entity->getScandeadline();
        }

        $builder->add('scandeadline','date',array(
            'widget' => 'single_text',
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control scandeadline-mask', 'style'=>'margin-top: 0;'),
            'required' => false,
            'data' => $scandeadline,
            'label'=>'Scan Deadline:',
        ));
        
        $builder->add('returnoption', 'checkbox', array(
            'label'     => 'Return slide(s) by this date even if not scanned:',
            'required'  => false,
        ));

        $attr = array('class' => 'combobox combobox-width');
        $builder->add('provider', 'entity', array(
            'class' => 'OlegOrderformBundle:User',
            'label'=>'* Submitter:',
            'required' => true,
            //'read_only' => true,    //not working => disable by twig
            //'multiple' => true,
            'attr' => $attr,
        ));

        //TODO: fix it:
        $builder->add('proxyuser', 'entity', array(
            'class' => 'OlegOrderformBundle:User',
            'label'=>'Ordering Provider:',
            'required' => false,
            //'multiple' => true,
            'attr' => $attr,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.roles LIKE :roles')
                    ->setParameter('roles', '%' . 'ROLE_ORDERING_PROVIDER' . '%');
            },
        ));

        $builder->add('dataquality', 'collection', array(
            'type' => new DataQualityType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__dataquality__',
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
