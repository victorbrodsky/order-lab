<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SiteParametersType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('maxIdleTime',null,array(
            'label'=>'Max Idle Time (min):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('environment','choice',array(
            'label'=>'Environment:',
            'choices' => array("live"=>"live", "dev"=>"dev"),
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('siteEmail','email',array(
            'label'=>'Site Email:',
            'attr' => array('class'=>'form-control')
        ));

        //smtp
        $builder->add('smtpServerAddress',null,array(
            'label'=>'SMTP Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        //scan order DB
        $builder->add('dbServerAddress',null,array(
            'label'=>'ScanOrder DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('dbServerPort',null,array(
            'label'=>'ScanOrder DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('dbServerAccountUserName',null,array(
            'label'=>'ScanOrder DB Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('dbServerAccountPassword',null,array(
            'label'=>'ScanOrder DB Server Account Password:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('dbDatabaseName',null,array(
            'label'=>'ScanOrder Database Name:',
            'attr' => array('class'=>'form-control')
        ));

        //LDAP
        $builder->add('aDLDAPServerAddress',null,array(
            'label'=>'AD/LDAP Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('aDLDAPServerPort',null,array(
            'label'=>'AD/LDAP Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('aDLDAPServerOu',null,array(
            'label'=>'AD/LDAP Server OU:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('aDLDAPServerAccountUserName',null,array(
            'label'=>'AD/LDAP Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('aDLDAPServerAccountPassword',null,array(
            'label'=>'AD/LDAP Server Account Password:',
            'attr' => array('class'=>'form-control')
        ));

        //Co-Path DB
        $builder->add('coPathDBServerAddress',null,array(
            'label'=>'CoPath DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('coPathDBServerPort',null,array(
            'label'=>'CoPath DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('coPathDBAccountUserName',null,array(
            'label'=>'CoPath DB Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('coPathDBAccountPassword',null,array(
            'label'=>'CoPath DB Server Account Password:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('coPathDBName',null,array(
            'label'=>'CoPath Database Name:',
            'attr' => array('class'=>'form-control')
        ));

        //Aperio DB
        $builder->add('aperioeSlideManagerDBServerAddress',null,array(
            'label'=>'Aperio eSlide Manager DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('aperioeSlideManagerDBServerPort',null,array(
            'label'=>'Aperio eSlide Manager DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('aperioeSlideManagerDBUserName',null,array(
            'label'=>'Aperio eSlide Manager DB Server User Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('aperioeSlideManagerDBPassword',null,array(
            'label'=>'Aperio eSlide Manager DB Server Password:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('aperioeSlideManagerDBName',null,array(
            'label'=>'Aperio eSlide Manager Database Name:',
            'attr' => array('class'=>'form-control')
        ));

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SiteParameters'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_siteparameters';
    }
}
