<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

//use Oleg\UserdirectoryBundle\Form\PerSiteSettingsType;

class UserType extends AbstractType
{

    protected $cicle;
    protected $roleAdmin;
    protected $user;
    protected $roles;

    public function __construct( $cicle = null, $user, $roles, $roleAdmin = false )
    {
        $this->cicle = $cicle;
        $this->user = $user;
        $this->roleAdmin = $roleAdmin;
        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
//            $constraint = new UserPassword();
//        } else {
//            // Symfony 2.1 support with the old constraint class
//            $constraint = new OldUserPassword();
//        }

        $read_only = false;
        if( !$this->roleAdmin ) {
            $read_only = true;
        }

        $builder->add('username', null, array(
            'required' => true,
            'label' => 'User Name:',
            'read_only' => $read_only,
            'attr' => array('class'=>'form-control form-control-modif', 'required'=>'required')
        ));
        $builder->add('firstName', null, array(
            'label' => 'First Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('middleName', null, array(
            'label' => 'Middle Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('lastName', null, array(
            'label' => 'Last Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('email', 'email', array(
            'label' => 'Email:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('displayName', null, array(
            'label' => 'Display Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('preferredPhone', null, array(
            'label' => 'Preferred Phone Number:',
            'attr' => array('class'=>'form-control form-control-modif phone-mask')
        ));

        //timezone
//        $tzUtil = new TimeZoneUtil();
//        $builder->add( 'timezone', 'choice', array(
//            'label' => 'Time Zone:',
//            'choices' => $tzUtil->tz_list(),
//            'required' => true,
//            'preferred_choices' => array('America/New_York'),
//            'attr' => array('class' => 'combobox combobox-width')
//        ));

        $builder->add('preferences', new UserPreferencesType(), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserPreferences',
            'label' => false,
            'required' => false,
        ));

        //Roles
        $attr = array('class' => 'combobox combobox-width');
        $builder->add('roles', 'choice', array(
            'choices' => $this->roles,
            'label' => 'Role(s):',
            'attr' => $attr,
            'multiple' => true,
        ));

        //hook for extended class
        $this->addHookFields($builder);

//        $builder->add('institution', 'entity', array(
//            'class' => 'OlegUserdirectoryBundle:Institution',
//            'label'=>'All Institution(s):',
//            'required' => false,
//            'multiple' => true,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'property' => 'name'
//            //'by_reference' => false //force to use setters of User entity
//        ));

//        $attr = array('class' => 'ajax-combobox-pathservice', 'type' => 'hidden');    //new
//        $builder->add('service', 'custom_selector', array(
//            'label' => 'All Service(s):',
//            'attr' => $attr,
//            'required' => false,
//            'classtype' => 'userPathologyServices'
//        ));


//        $builder->add('perSiteSettings', 'collection', array(
//            'type' => new PerSiteSettingsType(),
//            'label' => false,
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__persitesettings__',
//        ));

//        //Roles
//        $attr = array('class' => 'combobox combobox-width');
//
//        if( $this->cicle == "show" || $this->roleAdmin ) {
//
//            $builder->add('roles', 'choice', array(
//                'choices' => $this->roles,
//                'label' => 'Role(s):',
//                'attr' => $attr,
//                'multiple' => true,
//            ));
//
//            $builder->add('institution', 'entity', array(
//                'class' => 'OlegUserdirectoryBundle:Institution',
//                'label'=>'All Institution(s):',
//                'required' => false,
//                'multiple' => true,
//                'attr' => array('class' => 'combobox combobox-width'),
//                'property' => 'name'
//                //'by_reference' => false //force to use setters of User entity
//            ));
//
//        }

        if( $this->roleAdmin ) {
//            $builder->add('enabled', null, array(
//                'label' => 'Enabled',
//                'attr' => array('class'=>'form-control form-control-modif')
//            ));
            $builder->add('locked', null, array(
                'required' => false,
                'label' => 'Prevent user from logging in (lock):',
                'attr' => array('class'=>'form-control form-control-modif')
            ));
//            $builder->add('expired', null, array(
//                'label' => 'Expired',
//                'attr' => array('class'=>'form-control form-control-modif')
//            ));
        }

//        $attr = array('class' => 'ajax-combobox-pathservice', 'type' => 'hidden');    //new
//        $builder->add('chiefservices', 'custom_selector', array(
//            'label' => 'Chief of the following service(s):',
//            'attr' => $attr,
//            'required' => false,
//            'classtype' => 'userPathologyServices'
//        ));
//
//        if( $this->cicle == 'create' ) {
//            $builder->add('password', null, array(
//                'required' => true,
//                'label' => 'Password:',
//                'attr' => array('class'=>'form-control form-control-modif', 'required'=>'required')
//            ));
//        }


        //Administrative Titles
        $params = array('read_only'=>$read_only,'label'=>'Administrative Title','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdministrativeTitle','formname'=>'administrativetitletype');
        $builder->add('administrativeTitles', 'collection', array(
            'type' => new BaseTitleType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__administrativetitles__',
        ));

        $params = array('read_only'=>$read_only,'label'=>'Appointment Title','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AppointmentTitle','formname'=>'appointmenttitletype');
        $builder->add('appointmentTitles', 'collection', array(
            'type' => new BaseTitleType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__appointmenttitles__',
        ));

        $builder->add('locations', 'collection', array(
            'type' => new LocationType(),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__locations__',
        ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_userdirectorybundle_user';
    }

    public function addHookFields($builder) {
        //empty
    }

}
