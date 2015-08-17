<?php

namespace Oleg\UserdirectoryBundle\Form;

use Oleg\UserdirectoryBundle\Entity\Identifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ExaminationType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('scores', 'collection', array(
            'type' => new DocumentType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__document__',
        ));


        $builder->add('USMLEStep1DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('USMLEStep1Score', null, array(
            'label' => 'Score:',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('USMLEStep2CKDatePassed', null, array(
            'label' => 'CK - Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('USMLEStep2CKScore', null, array(
            'label' => 'CK - Score (optional):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('USMLEStep2CSDatePassed', null, array(
            'label' => 'CK - Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('USMLEStep2CSScore', null, array(
            'label' => 'CK - Score (optional):',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('USMLEStep3DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('USMLEStep3Score', null, array(
            'label' => 'Score (optional):',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('ECFMGCertificateNumber', null, array(
            'label' => 'ECFMG Certificate Number:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('ECFMGCertificateDate', null, array(
            'label' => 'Date ECFMG Certificate Granted:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('ECFMGCertificate', 'checkbox', array(
            'label' => false,
            'attr' => array('class'=>'form-control fellapp-ecfmgcertificate-field', 'onclick'=>'showHideWell(this)')
        ));


        $builder->add('COMLEXLevel1DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('COMLEXLevel1Score', null, array(
            'label' => 'Score:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('COMLEXLevel2DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('COMLEXLevel2Score', null, array(
            'label' => 'Score (optional):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('COMLEXLevel3DatePassed', null, array(
            'label' => 'Date passed:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));
        $builder->add('COMLEXLevel3Score', null, array(
            'label' => 'Score (optional):',
            'attr' => array('class'=>'form-control')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Examination',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_examination';
    }
}
