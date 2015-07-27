<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class UserRequestType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $helper = new FormHelper();
        
        $builder->add( 'cwid', 'text', array(
                'label'=>'WCMC CWID:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif cwid'),
        ));

        //hascwid
        $builder->add( 'hascwid', 'choice', array(
            'label'=>'Do you (the requester) have a CWID username?',
            'choices' => array("Yes"=>"Yes", "No"=>"No"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type hascwid')
        ));

        //password
        $builder->add( 'password', 'password', array(
            'mapped' => false,
            'label'=>'Password:',
            'attr' => array('class' => 'form-control form-control-modif cwid-password')
        ));
        
        $builder->add( 'name', 'text', array(
                'label'=>'Name:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'email', 'email', array(
                'label'=>'* Email:',
                'required'=> true, //does not work here
                'attr' => array('class'=>'form-control form-control-modif email-mask', 'required'=>'required'), //'required'=>'required' does not work here
        ));
        
        $builder->add( 'phone', 'text', array(
                'label'=>'Phone Number:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif phone-mask'),
        ));
        
        $builder->add( 'job', 'text', array(
                'label'=>'Job title:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));

        //requestedInstitutionalPHIScope
        if( array_key_exists('requestedInstitutionalPHIScope', $this->params) ) {
            $requestedInstitutionalPHIScope = $this->params['requestedInstitutionalPHIScope'];
        } else {
            $requestedInstitutionalPHIScope = null;
        }
        //echo "choices=".count($requestedInstitutionalPHIScope)."<br>";
        $builder->add('requestedInstitutionalPHIScope', 'entity', array(
            'label' => 'Institutional PHI Scope:',
            'required'=> true,
            'multiple' => true,
            'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $requestedInstitutionalPHIScope,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));


        
        $builder->add('request', 'textarea', array(
            'label'=>'Reason for account request:',
            'required'=> false,
            'attr' => array('class'=>'textarea form-control form-control-modif'),
        ));


        //$refLabel = "For reference, please provide the name and contact information of your supervisor or of the person who can confirm the validity of your request below.\r\nAccess permissions similar to (user name):";
        $builder->add( 'similaruser', 'text', array(
            'label' => "Access permissions similar to (user name):",
            'required' => false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referencename', 'text', array(
            'label'=>'Reference Name:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referenceemail', 'text', array(
            'label'=>'Reference Email:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referencephone', 'text', array(
            'label'=>'Reference Phone Number:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\UserRequest',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_userrequesttype';
    }
}
