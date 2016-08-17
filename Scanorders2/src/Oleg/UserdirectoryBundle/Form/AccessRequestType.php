<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolverInterface;
//use Doctrine\ORM\EntityRepository;
//use Symfony\Component\Form\FormEvents;
//use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AccessRequestType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add( 'email', 'email', array(
                'label'=>'* Email:',
                'required'=> true, //does not work here
                'attr' => array('class'=>'form-control email-mask', 'required'=>'required'), //'required'=>'required' does not work here
        ));
        
        $builder->add( 'phone', 'text', array(
                'label'=>'Phone Number:',
                'required'=> false,
                'attr' => array('class'=>'form-control phone-mask'),
        ));
        
        $builder->add( 'job', 'text', array(
                'label'=>'Job title:',
                'required'=> false,
                'attr' => array('class'=>'form-control'),
        ));


        if (array_key_exists('requestedScanOrderInstitutionScope', $this->params)) {
            $requestedScanOrderInstitutionScope = $this->params['requestedScanOrderInstitutionScope'];
        } else {
            $requestedScanOrderInstitutionScope = null;
        }
        $builder->add('organizationalGroup', 'entity', array(
            'label' => 'Organizational Group:',
            'required' => false,
            'multiple' => false,
            'property' => 'getNodeNameWithRoot',
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $requestedScanOrderInstitutionScope,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));


//        //requestedScanOrderInstitutionScope
//    if(1) {
//        if (array_key_exists('requestedScanOrderInstitutionScope', $this->params)) {
//            $requestedScanOrderInstitutionScope = $this->params['requestedScanOrderInstitutionScope'];
//        } else {
//            $requestedScanOrderInstitutionScope = null;
//        }
//        //echo "choices=".count($requestedScanOrderInstitutionScope)."<br>";
//        $builder->add('organizationalGroup', 'entity', array(
//            'label' => '* Organizational Group:',
//            'required' => false,
//            'multiple' => false,
//            //'empty_value' => false,
//            'property' => 'getNodeNameWithRoot',
//            'class' => 'OlegUserdirectoryBundle:Institution',
//            'choices' => $requestedScanOrderInstitutionScope,
//            'attr' => array('class' => 'combobox combobox-width combobox-institution')
//        ));
//    } else {
//        ///////////////////////// tree node /////////////////////////
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $title = $event->getData();
//            $form = $event->getForm();
//
//            $label = null;
//            if( $title ) {
//                $institution = $title->getOrganizationalGroup();
//                if( $institution ) {
//                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
//                }
//            }
//            if( !$label ) {
//                $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
//            }
//
//            $form->add('organizationalGroup', 'employees_custom_selector', array(
//                'label' => "Organizational Group (".$label."):",
//                'required' => true,
//                'attr' => array(
//                    'class' => 'ajax-combobox-compositetree',
//                    'type' => 'hidden',
//                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
//                    'data-compositetree-classname' => 'Institution'
//                ),
//                'classtype' => 'institution'
//            ));
//        });
//        ///////////////////////// EOF tree node /////////////////////////
//    }


        
        $builder->add('reason', 'textarea', array(
            'label'=>'Reason for access request:',
            'required'=> false,
            'attr' => array('class'=>'textarea form-control'),
        ));


        //$refLabel = "For reference, please provide the name and contact information of your supervisor or of the person who can confirm the validity of your request below.\r\nAccess permissions similar to (user name):";
        $builder->add( 'similaruser', 'text', array(
            'label' => "Access permissions similar to (user name):",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'referencename', null, array(
            'label'=>'Reference Name:',
            'required'=> false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'referenceemail', null, array(
            'label'=>'Reference Email:',
            'required'=> false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'referencephone', null, array(
            'label'=>'Reference Phone Number:',
            'required'=> false,
            'attr' => array('class'=>'form-control'),
        ));

    }

    //public function setDefaultOptions(OptionsResolverInterface $resolver)
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\AccessRequest',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_accessrequesttype';
    }
}
