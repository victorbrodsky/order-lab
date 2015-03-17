<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

use Oleg\OrderformBundle\Helper\FormHelper;


class MessageType extends AbstractType
{

    protected $entity;
    protected $params;
    protected $labels;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;

        if( !array_key_exists('type', $this->params) ) {
            $this->params['type'] = 'Unknown Order';
        }

        //default labels
        $labels = array(
            'educational' => 'Educational:',
            'research' => 'Research:',
            'institution' => 'Institution:',
            'destinations' => 'Return Slides to:',
            'equipment' => 'Scanner:',
            'proxyuser' => '',
            'returnoption' => 'Return slide(s) by this date even if not scanned:',
            'priority' => 'Priority:',
            'deadline' => 'Deadline:',
        );

        //over write labels
        if( array_key_exists('labels', $this->params) ) {
            $overLabels = $this->params['labels'];
            foreach($labels as $field=>$label) {
                //echo $field."=>".$label."<br>";
                if( array_key_exists($field, $overLabels) ) {
                    //echo $field." exists!<br>";
                    $labels[$field] = $overLabels[$field];
                }
            }

            $this->labels = $labels;
        }

    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( array_key_exists('idnumber', $this->params) &&  $this->params['idnumber'] == true ) {
            $builder->add('idnumber', null, array(
                'label' => "Identification Number:",
                'attr' => array('class' => 'form-control'),
                'required'=>false,
            ));
        }

        if( array_key_exists('educational', $this->params) &&  $this->params['educational'] == true ) {
            $builder->add( 'educational', new EducationalType($this->params,$this->entity), array('label'=>$this->labels['educational']) );
        }

        if( array_key_exists('research', $this->params) &&  $this->params['research'] == true ) {
            $builder->add( 'research', new ResearchType($this->params,$this->entity), array('label'=>$this->labels['research']) );
        }

        //priority
        $helper = new FormHelper();
        $priorityArr = array(
            'label' => $this->labels['priority'],
            'choices' => $helper->getPriority(),
            'required' => true,
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type', 'required'=>'required')
        );
        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create' ) {
            $priorityArr['data'] = 'Routine';    //new
        }
        $builder->add( 'priority', 'choice', $priorityArr);

        //deadline
        if( $this->params['cycle'] == 'new' ) {
            $deadline = date_modify(new \DateTime(), '+2 week');
        } else {
            $deadline = null;
        }

        if( $this->entity && $this->entity->getDeadline() != '' ) {
            $deadline = $this->entity->getDeadline();
        }

        $builder->add('deadline','date',array(
            'widget' => 'single_text',
            'format' => 'MM-dd-yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'data' => $deadline,
            'label'=>$this->labels['deadline'],
        ));

        $builder->add('returnoption', 'checkbox', array(
            'label'     => $this->labels['returnoption'],
            'required'  => false,
        ));


        $builder->add('proxyuser', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=>$this->labels['equipment'],
            'required' => false,
            //'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.roles LIKE :roles OR u=:user')
                    ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
            },
        ));

        $builder->add( 'equipment', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Equipment',
            'property' => 'name',
            'label'=>$this->labels['equipment'],
            'required'=> true,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->leftJoin('i.keytype','keytype')
                        ->where("keytype.name = :keytype AND i.type != :type")
                        ->setParameters( array('keytype' => 'Whole Slide Scanner', 'type' => 'disabled') );
                },
        ));


        //Endpoint object: destination - location
        $this->params['label'] = $this->labels['destinations'];
        $builder->add('destinations', 'collection', array(
            'type' => new EndpointType($this->params,$this->entity),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__destinations__',
        ));

        //Institution Tree
        if( array_key_exists('institutions', $this->params) ) {
            $institutions = $this->params['institutions'];
        } else {
            $institutions = null;
        }

        $builder->add('institution', 'entity', array(
            'label' => $this->labels['institution'],
            'required'=> true,
            'multiple' => false,
            'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $institutions,
            'attr' => array('class' => 'combobox combobox-width combobox-institution ajax-combobox-institution-preset')
        ));


        //message's slide
        if( array_key_exists('slide', $this->params) &&  $this->params['slide'] == true ) {
            $builder->add('slide', 'collection', array(
                'type' => new SlideSimpleType($this->params,$this->entity),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__slide__',
            ));
        }

        //message's laborder
        if( array_key_exists('laborder', $this->params) &&  $this->params['laborder'] == true ) {
            $builder->add('laborder', new LabOrderType($this->params,$this->entity), array(
                'required' => false,
                'label' => false
            ));
        }

        //message's laborder
        if( array_key_exists('message.report', $this->params) &&  $this->params['message.report'] == true ) {
            $builder->add('report', new ReportType($this->params,$this->entity), array(
                'required' => false,
                'label' => false
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

    //return true if substring is found: 'Scan Order', 'Lab Order' ...
    public function hasSpecificOrders( $orderinfo, $substring ) {
        $category = $orderinfo->getType();
        //echo "category=".$category."<br>";
        if( strpos($category,$substring) !== false ) {
            //echo "has <br>";
            return true;
        }
        //echo "no has <br>";
        return false;
    }

}
