<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SiteParametersType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $always_empty = true;
//
//        if( $this->params['cicle'] != 'show' ) {
//            $always_empty = false;
//        }

        //echo "$always_empty=".$always_empty."<br>";

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'maxIdleTime' )
        $builder->add('maxIdleTime',null,array(
            'label'=>'Max Idle Time (min):',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'environment' )
        $builder->add('environment','choice',array(
            'label'=>'Environment:',
            'choices' => array("live"=>"live", "dev"=>"dev"),
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'siteEmail' )
        $builder->add('siteEmail','email',array(
            'label'=>'Site Email:',
            'attr' => array('class'=>'form-control')
        ));

        //smtp
        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'smtpServerAddress' )
        $builder->add('smtpServerAddress',null,array(
            'label'=>'SMTP Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        //scan order DB
        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'dbServerAddress' )
        $builder->add('dbServerAddress',null,array(
            'label'=>'ScanOrder DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'dbServerPort' )
        $builder->add('dbServerPort',null,array(
            'label'=>'ScanOrder DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'dbServerAccountUserName' )
        $builder->add('dbServerAccountUserName',null,array(
            'label'=>'ScanOrder DB Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'dbServerAccountPassword' )
        $builder->add('dbServerAccountPassword',null,array(
            'label'=>'ScanOrder DB Server Account Password:',
            //'always_empty' => $always_empty,
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'dbDatabaseName' )
        $builder->add('dbDatabaseName',null,array(
            'label'=>'ScanOrder Database Name:',
            'attr' => array('class'=>'form-control')
        ));

        //LDAP
        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aDLDAPServerAddress' )
        $builder->add('aDLDAPServerAddress',null,array(
            'label'=>'AD/LDAP Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aDLDAPServerPort' )
        $builder->add('aDLDAPServerPort',null,array(
            'label'=>'AD/LDAP Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aDLDAPServerOu' )
        $builder->add('aDLDAPServerOu',null,array(
            'label'=>'AD/LDAP Server OU:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aDLDAPServerAccountUserName' )
        $builder->add('aDLDAPServerAccountUserName',null,array(
            'label'=>'AD/LDAP Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aDLDAPServerAccountPassword' )
        $builder->add('aDLDAPServerAccountPassword',null,array(
            'label'=>'AD/LDAP Server Account Password:',
            //'always_empty' => $always_empty,
            'attr' => array('class'=>'form-control')
        ));

        //Co-Path DB
        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'coPathDBServerAddress' )
        $builder->add('coPathDBServerAddress',null,array(
            'label'=>'CoPath DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'coPathDBServerPort' )
        $builder->add('coPathDBServerPort',null,array(
            'label'=>'CoPath DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'coPathDBAccountUserName' )
        $builder->add('coPathDBAccountUserName',null,array(
            'label'=>'CoPath DB Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'coPathDBAccountPassword' )
        $builder->add('coPathDBAccountPassword',null,array(
            'label'=>'CoPath DB Server Account Password:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'coPathDBName' )
        $builder->add('coPathDBName',null,array(
            'label'=>'CoPath Database Name:',
            'attr' => array('class'=>'form-control')
        ));

        //Aperio DB
        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBServerAddress' )
        $builder->add('aperioeSlideManagerDBServerAddress',null,array(
            'label'=>'Aperio eSlide Manager DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBServerPort' )
        $builder->add('aperioeSlideManagerDBServerPort',null,array(
            'label'=>'Aperio eSlide Manager DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBUserName' )
        $builder->add('aperioeSlideManagerDBUserName',null,array(
            'label'=>'Aperio eSlide Manager DB Server User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBPassword' )
        $builder->add('aperioeSlideManagerDBPassword',null,array(
            'label'=>'Aperio eSlide Manager DB Server Password:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cicle'] == 'show' || $this->params['param'] == 'aperioeSlideManagerDBName' )
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
