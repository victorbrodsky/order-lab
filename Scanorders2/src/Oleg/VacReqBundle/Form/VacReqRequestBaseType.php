<?php

namespace Oleg\VacReqBundle\Form;


use Oleg\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class VacReqRequestBaseType extends AbstractType
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

        $builder->add('startDate', 'date', array(
            'label' => 'Start Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control vacreq-startDate'),
        ));

        $builder->add('endDate', 'date', array(
            'label' => 'End Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control vacreq-endDate'),
        ));


        $builder->add('numberOfDays', null, array(
            'label' => 'Number of Work Days Offsite (Please do not include holidays):',
            'attr' => array('class'=>'form-control vacreq-numberOfDays')
        ));

        $builder->add('firstDayBackInOffice', 'date', array(
            'label' => 'First Day Back in Office:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control vacreq-firstDayBackInOffice'),
        ));

        if( $this->params['cycle'] == 'edit' || $this->params['cycle'] == 'show' ) {
            $builder->add('approverComment', 'textarea', array(
                'label' => 'Approver Comment:',
                'required' => false,
                'attr' => array('class' => 'textarea form-control'),
            ));
        }

        if( $this->params['cycle'] != 'new' ) {
            $builder->add('status', 'choice', array(
                'disabled' => ($this->params['roleAdmin'] ? false : true),
                'choices' => array(
                    //'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected'
                ),
                'label' => false,   //"Status:",
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                //'data' => 'pending',
                'attr' => array('class' => 'horizontal_type_wide'), //horizontal_type
            ));
        }

    }

}
