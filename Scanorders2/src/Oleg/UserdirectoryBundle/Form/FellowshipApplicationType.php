<?php

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Form\DataTransformer\StringToBooleanTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Training;

class FellowshipApplicationType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        //print_r($this->params);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('fellowshipSubspecialty',null, array(
            'label' => 'Fellowship Type:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
        ));

//        $builder->add('timestamp','date',array(
//            'widget' => 'single_text',
//            'label' => "Creation Date:",
//            'format' => 'MM/dd/yyyy, H:mm:ss',
//            'attr' => array('class' => 'datepicker form-control'),
//            'required' => false,
//        ));

        $builder->add('startDate','date',array(
            'widget' => 'single_text',
            'label' => "Start Date:",
            'format' => 'MM/dd/yyyy',  //'MM/dd/yyyy, H:mm:ss',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('endDate','date',array(
            'widget' => 'single_text',
            'label' => "End Date:",
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('user', new FellAppUserType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'label' => false,
            'required' => false,
        ));

        $builder->add('coverLetters', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Cover Letter(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__document__',
        ));


//        $builder->add('reprimand','choice', array(
//            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
//            'required' => false,
//            'choices' => array('Yes'=>'Yes','No'=>'No'),
//            'attr' => array('class' => 'combobox'),
//        ));
        $builder->add('reprimand','checkbox', array(
            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
            'required' => false,
            'attr' => array('class' => 'form-control fellapp-reprimand-field', 'onclick'=>'showHideWell(this)'),
        ));
        $builder->get('reprimand')->addModelTransformer(new StringToBooleanTransformer());
        $builder->add('reprimandDocuments', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Upload Reprimand Explanation(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__document__',
        ));

//        $builder->add('lawsuit','choice', array(
//            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
//            'required' => false,
//            'choices' => array('Yes'=>'Yes','No'=>'No'),
//            'attr' => array('class' => 'combobox'),
//        ));
        $builder->add('lawsuit','checkbox', array(
            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
            'required' => false,
            'attr' => array('class' => 'form-control fellapp-lawsuit-field', 'onclick'=>'showHideWell(this)'),
        ));
        $builder->get('lawsuit')->addModelTransformer(new StringToBooleanTransformer());
        $builder->add('lawsuitDocuments', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Upload Legal Explanation(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__document__',
        ));


        $builder->add('references', 'collection', array(
            'type' => new ReferenceType($this->params),
            'label' => 'Reference(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__references__',
        ));


        $builder->add('honors',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('publications',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('memberships',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));



        $builder->add('signatureName',null, array(
            'label' => 'Signature:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('signatureDate', null, array(
            'label' => 'Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));




    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\FellowshipApplication',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_fellowshipapplication';
    }
}
