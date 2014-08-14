<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



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
            'data_class' => 'Oleg\OrderformBundle\Entity\UserPreferences',
            'label' => false,
            'required' => false,
        ));


        $attr = array('class' => 'ajax-combobox-pathservice', 'type' => 'hidden');    //new
        $builder->add('division', 'custom_selector', array(
            'label' => 'Departmental Division(s)/Service(s):',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'userPathologyServices'
        ));


        //Roles
        $attr = array('class' => 'combobox combobox-width');

        if( $this->cicle == "show" || $this->roleAdmin ) {

            $builder->add('roles', 'choice', array(
                'choices' => $this->roles,
                'label' => 'Role(s):',
                'attr' => $attr,
                'multiple' => true,
            ));

            $builder->add('institution', 'entity', array(
                'class' => 'OlegOrderformBundle:Institution',
                'label'=>'Institution(s):',
                'required' => false,
                'multiple' => true,
                'attr' => array('class' => 'combobox combobox-width'),
                'property' => 'name'
                //'by_reference' => false //force to use setters of User entity
            ));

        }

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

        $attr = array('class' => 'ajax-combobox-pathservice', 'type' => 'hidden');    //new
        $builder->add('chiefservices', 'custom_selector', array(
            'label' => 'Chief of the following service(s):',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'userPathologyServices'
        ));

        if( $this->cicle == 'create' ) {
            $builder->add('password', null, array(
                'required' => true,
                'label' => 'Password:',
                'attr' => array('class'=>'form-control form-control-modif', 'required'=>'required')
            ));
        }


        //Admnistrative Titles
//        $builder->add('title', null, array(
//            'label' => 'Primary Job Title:',
//            'attr' => array('class'=>'form-control form-control-modif')
//        ));
        $builder->add('admnistrativeTitles', 'collection', array(
            'type' => new AdministrativeTitleType(),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__admnistrativeTitles__',
        ));

        //Main Office Location
//        $builder->add('phone', null, array(
//            'label' => 'Preferred Phone Number:',
//            'attr' => array('class'=>'form-control form-control-modif')
//        ));
//        $builder->add('fax', null, array(
//            'label' => 'Fax:',
//            'attr' => array('class'=>'form-control form-control-modif')
//        ));
//        $builder->add('office', null, array(
//            'label' => 'Main Office Room Number:',
//            'attr' => array('class'=>'form-control form-control-modif')
//        ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_user';
    }

}
