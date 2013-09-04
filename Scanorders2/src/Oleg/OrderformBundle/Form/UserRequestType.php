<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'phone', 'text', array(
                'label'=>'Phone Number:',
                'max_length'=>'20',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
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
        
        $builder->add( 'pathologyService', 'choice', array(
            'label' => 'Service / Division:',
            'max_length'=>200,
            'choices' => $helper->getPathologyService(),
            'required'=>false,
            'attr' => array('class' => 'combobox combobox-width'),
        ));
        
        $builder->add('request', 'textarea', array(
            'label'=>'Reason for account request:',
            'max_length'=>'1000',
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
