<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

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
        //echo "type=".$this->params['type']."<br>";
        //echo "cicle=".$this->params['cicle']."<br>";
//        echo "<br>";

        $helper = new FormHelper();

        $builder->add( 'oid' , 'hidden', array('attr'=>array('class'=>'orderinfo-id')) );

        //unmapped data quality form to record the MRN-Accession conflicts
        $builder->add('conflicts', 'collection', array(
            'mapped' => false,
            'type' => new DataQualityMrnAccType($this->params, null),
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__dataqualitymrnacc__',
        ));

        //add children
        //if( $this->params['type'] != 'Table-View Scan Order' || ($this->params['type'] == 'Table-View Scan Order' && $this->params['cicle'] != 'new') ) {
        if( $this->params['type'] != 'Table-View Scan Order' ) {

            //echo "orderinfo type: show patient <br>";
            $builder->add('patient', 'collection', array(
                'type' => new PatientType($this->params,$this->entity),    //$this->type),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patient__',
            ));

        } else {

            //echo "orderinfo type: show datalocker <br>";

            $builder->add('datalocker','hidden', array(
                "mapped" => false
            ));

            $builder->add('clickedbtn','hidden', array(
                "mapped" => false
            ));

        }

        //echo "<br>type=".$this->type."<br>";

        $builder->add( 'educational', new EducationalType($this->params,$this->entity), array('label'=>'Educational:') );

        $builder->add( 'research', new ResearchType($this->params,$this->entity), array('label'=>'Research:') );

        //priority
        $priorityArr = array(
            'label' => 'Priority:',
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
            'label' => 'Slide Delivery:',
            'attr' => $attr,
            'required'=>true,
            'classtype' => 'slideDelivery'
        ));

        $attr = array('class' => 'ajax-combobox-return', 'type' => 'hidden');
        $builder->add('returnSlide', 'custom_selector', array(
            'label' => 'Return Slides to:',
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
            'format' => 'MM-dd-yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control scandeadline-mask', 'style'=>'margin-top: 0;'),
            'required' => false,
            'data' => $scandeadline,
            'label'=>'Scan Deadline:',
        ));

        $builder->add('returnoption', 'checkbox', array(
            'label'     => 'Return slide(s) by this date even if not scanned:',
            'required'  => false,
        ));


        //$builder->add( 'provider', new ProviderType(), array('label'=>'Submitter:') );
//        $builder->add( 'provider', null, array(
//            'label'=>'Submitter:',
//            'read_only' => true,
//            'attr' => array('class'=>'form-control form-control-modif')
//        ));

        $builder->add('proxyuser', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=>'Ordering Provider:',
            'required' => false,
            //'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.roles LIKE :roles OR u=:user')
                    ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
            },
        ));

        $builder->add( 'scanner', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Equipment',
            'property' => 'name',
            'label'=>'Scanner:',
            'required'=> true,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width combobox-scanner'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->leftJoin('i.keytype','keytype')
                        ->where("keytype.name = :keytype AND i.type != :type")
                        ->setParameters( array('keytype' => 'Whole Slide Scanner', 'type' => 'disabled') );
                },
        ));

        $builder->add( 'purpose', 'choice', array(
            'label'=>'Purpose:',
            'required' => true,
            'choices' => array("For Internal Use by WCMC Department of Pathology"=>"For Internal Use by WCMC Department of Pathology", "For External Use (Invoice Fund Number)"=>"For External Use (Invoice Fund Number)"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type')
        ));

        $attr = array('class' => 'ajax-combobox-account', 'type' => 'hidden');
        $builder->add('account', 'custom_selector', array(
            'label' => 'Debit Fund WBS Account Number:',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'account'
        ));



        //new fields
//        $attr = array('class' => 'ajax-combobox-department', 'type' => 'hidden');
//        $builder->add('department', 'custom_selector', array(
//            'label' => 'Department:',
//            'attr' => $attr,
//            'required' => false,
//            'classtype' => 'department'
//        ));
        //allow to select only one service
//        $attr = array('class' => 'ajax-combobox-service', 'type' => 'hidden');  //ajax-combobox-pathservice
//        $builder->add('service', 'custom_selector', array(
//            'label' => 'Service:',
//            'attr' => $attr,
//            'required' => false,
//            'classtype' => 'pathologyService'
//        ));
//        $builder->add( 'institution', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:Institution',
//            'property' => 'name',
//            'label'=>'Institution:',
//            'required'=> true,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width combobox-institution'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('i')
//                        ->innerJoin('i.users', 'user')
//                        ->where('user = :user')
//                        ->setParameters( array(
//                            'user' => $this->params['user'],
//                        ));
//                },
//        ));

        if( array_key_exists('institutions', $this->params) ) {
            $institutions = $this->params['institutions'];
        } else {
            $institutions = null;
        }

        $builder->add('institution', 'entity', array(
            'label' => 'Institution:',
            'required'=> true,
            'multiple' => false,
            'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $institutions, //$this->params['institutions'],
            'attr' => array('class' => 'combobox combobox-width combobox-institution ajax-combobox-institution-preset')
        ));

        if( $this->params['cicle'] != 'show' ) {

            if( array_key_exists('department', $this->params) ) {
                $departmentId = $this->params['department']->getId();
            } else {
                $departmentId = null;
            }

            //department. User should be able to add institution to administrative or appointment titles
            $builder->add('department', 'employees_custom_selector', array(
                'label' => "Department:",
                "mapped" => false,
                'required' => false,
                'data' => $departmentId,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-department combobox-without-add', 'type' => 'hidden'),
                'classtype' => 'department'
            ));


            if( array_key_exists('division', $this->params) ) {
                $divisionId = $this->params['division']->getId();
            } else {
                $divisionId = null;
            }

            //division. User should be able to add institution to administrative or appointment titles
            $builder->add('division', 'employees_custom_selector', array(
                'label' => "Division:",
                "mapped" => false,
                'required' => false,
                'data' => $divisionId,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-division combobox-without-add', 'type' => 'hidden'),
                'classtype' => 'division'
            ));
        }

        if( $this->params['cicle'] == 'show' ) {
            //echo "entity service";
            $builder->add('service', 'entity', array(
                'label' => 'Service:',
                'required'=> false,
                'multiple' => false,
                'class' => 'OlegUserdirectoryBundle:Service',
                //'choices' => $this->params['services'],
                'attr' => array('class' => 'combobox combobox-width')
            ));
        } else {
            //service. User should be able to add institution to administrative or appointment titles
            $builder->add('service', 'employees_custom_selector', array(
                'label' => "Service:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-service combobox-without-add', 'type' => 'hidden'),
                'classtype' => 'service'
            ));
        }
        
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
