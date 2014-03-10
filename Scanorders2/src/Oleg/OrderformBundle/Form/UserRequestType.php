<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Oleg\OrderformBundle\Helper\FormHelper;

class UserRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        $helper = new FormHelper();
        
        $builder->add( 'cwid', 'text', array(
                'label'=>'WCMC CWID:',
                'max_length'=>'10',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));

        //hascwid
        $builder->add( 'hascwid', 'choice', array(
            'label'=>'Do you (the requester) have a CWID username?',
            //'max_length'=>20,
            //'required'=>false,
            'choices' => array("Yes"=>"Yes", "No"=>"No"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type hascwid')
        ));
        
        $builder->add( 'name', 'text', array(
                'label'=>'Name:',
                'max_length'=>'500',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'email', 'email', array(
                'label'=>'* Email:',
                'max_length'=>'200',
                'required'=> true,
                'attr' => array('class'=>'form-control form-control-modif email-mask', 'required'=>'true'),
        ));
        
        $builder->add( 'phone', 'text', array(
                'label'=>'Phone Number:',
                'max_length'=>'20',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif phone-mask'),
        ));
        
        $builder->add( 'job', 'text', array(
                'label'=>'Job title:',
                'max_length'=>'200',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'organization', 'text', array(
                'label'=>'Organization:',
                'max_length'=>'200',
                'required'=> false,
                'data'=>'Weill Cornell Medical College',
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'department', 'text', array(
                'label'=>'Department:',
                'max_length'=>'200',
                'required'=> false,
                'data'=>'Department of Pathology and Laboratory Medicine',
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
//        $builder->add( 'pathologyService', 'choice', array(
//            'label' => 'Service / Division:',
//            'max_length'=>200,
//            'choices' => $helper->getPathologyService(),
//            'required'=>false,
//            'attr' => array('class' => 'combobox combobox-width', 'style'=>'width: 70%;'),
//        ));
        $attr = array('class' => 'ajax-combobox-pathservice', 'type' => 'hidden');    //new
        $builder->add('pathologyServices', 'custom_selector', array(
            'label' => 'Departmental Division(s) / Service(s):',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'userPathologyServices'
        ));
        
        $builder->add('request', 'textarea', array(
            'label'=>'Reason for account request:',
            'required'=> false,
            'attr' => array('class'=>'textarea form-control form-control-modif'),
        ));

//        $attr = array('class' => 'combobox combobox-width ');
//        $builder->add('similaruser', 'entity', array(
//            'class' => 'OlegOrderformBundle:User',
//            'label'=>'Access permissions similar to:',
//            'required' => false,
//            //'multiple' => true,
//            'attr' => $attr,
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('u')
//                    ->where('u.username <> :username')
//                    ->setParameter('username', 'system');
//            },
//        ));

        //$refLabel = "For reference, please provide the name and contact information of your supervisor or of the person who can confirm the validity of your request below.\r\nAccess permissions similar to (user name):";
        $builder->add( 'similaruser', 'text', array(
            'label' => "Access permissions similar to (user name):",
            'required' => false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add('creationdate');

//        $builder->add("cwidyesno", "choice", array(
//            'mapped' => false,
//            'multiple' => false,
//            'expanded' => true,
//            'label' => false,
//            'choices' => array("Yes"=>"Yes", "No"=>"No"),
//            'attr' => array('class' => 'horizontal_type cwidyesno')
//        ));

        $builder->add( 'referencename', 'text', array(
            'label'=>'Reference Name:',
            'required'=> false,
//            'attr' => array('class'=>'form-control form-control-modif element-with-tooltip', 'data-toggle'=>'tooltip', 'title' => 'name of your supervisor or of the person who can confirm the validity of your request'),
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
            'data_class' => 'Oleg\OrderformBundle\Entity\UserRequest'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_userrequesttype';
    }
}
