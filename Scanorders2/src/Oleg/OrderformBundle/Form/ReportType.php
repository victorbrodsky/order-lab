<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Oleg\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ReportType extends AbstractType
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

        $builder->add('issuedDate', 'date', array(
            'label' => "Issued Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('receivedDate', 'date', array(
            'label' => "Received Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('signatureDate', 'date', array(
            'label' => "Signature Date & Time:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));




        //Requisition Form Image container
//        $params = array(
//            'labelPrefix' => 'Reference Representation',
//            'documentContainer.comments.comment.label' => "Full Text:"
//        );
//        $builder->add('documentContainer', new DocumentContainerType($params), array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentContainer',
//            'label' => false
//        ));

        //$paramsNew = new ArrayObject($params);
//        $params = array('labelPrefix'=>'Signing Pathologist(s):');
//        $builder->add('signingPathologists', 'collection', array(
//            'type' => new UserWrapperType($params),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__signingpathologists__',
//        ));
//
//        $params = array('labelPrefix'=>'Consulted Pathologist(s):');
//        $builder->add('consultedPathologists', 'collection', array(
//            'type' => new UserWrapperType($params),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__consultedpathologists__',
//        ));


//        $builder->add('reportType', null, array(
//            'label' => "Report Type:",
//            'required' => false,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width'),
//        ));


//        $builder->add('consultedPathologists', 'custom_selector', array(
//            'label' => 'Course Director(s):',
//            'attr' => array('class' => 'combobox combobox-width combobox-optionaluser-educational', 'type' => 'hidden'),
//            'required'=>false,
//            'classtype' => 'optionalUserEducational'
//        ));






//        $builder->add('others', new ArrayFieldType(), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\Report',
//            'label' => false,
//			'attr' => array('style'=>'display:none;')
//        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Report',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_reporttype';
    }
}
