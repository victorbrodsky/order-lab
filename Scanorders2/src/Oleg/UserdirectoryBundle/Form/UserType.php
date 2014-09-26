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

use Doctrine\ORM\EntityRepository;
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

        $read_only = false;
        //$readonlyAttr = 'false';
        if( !$this->roleAdmin ) {
            $read_only = true;
            //$readonlyAttr = 'true';
        }
        //echo "read_only=".$read_only."<br>";

        $attr = array('class'=>'combobox combobox-width user-keytype-field');
        if( $read_only ) {
            $attr['readonly'] = 'readonly';
        }
        $builder->add('keytype', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:UsernameType',
            'read_only' => $read_only,
            'property' => 'name',
            'label' => 'Primary Public User ID Type:',
            'required' => false,
            'multiple' => false,
            'attr' => $attr,    //array('class'=>'combobox combobox-width user-keytype-field','readonly'=>$readonlyAttr ),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));

//        if( $this->roleAdmin ) {
//            $builder->add('username', null, array(
//                'label' => 'Unique Username:',
//                'read_only' => true,
//                'attr' => array('class'=>'form-control form-control-modif')
//            ));
//        }

        $builder->add('primaryPublicUserId', null, array(
            'label' => 'Primary Public User ID:',
            'read_only' => $read_only,
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('firstName', null, array(
            'label' => 'First Name:',
            'attr' => array('class'=>'form-control form-control-modif') //'required'=>'required'
        ));
        $builder->add('middleName', null, array(
            'label' => 'Middle Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('lastName', null, array(
            'label' => 'Last Name:',
            'attr' => array('class'=>'form-control form-control-modif') //'required'=>'required'
        ));
        $builder->add('email', 'email', array(
            'label' => 'Preferred Email:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('displayName', null, array(
            'label' => 'Preferred Display Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('preferredPhone', null, array(
            'label' => 'Preferred Phone Number:',
            'attr' => array('class'=>'form-control form-control-modif phone-mask')
        ));

        $builder->add('preferences', new UserPreferencesType(), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserPreferences',
            'label' => false,
            'required' => false,
        ));

        //Roles
        if( $this->cicle == "show" || $this->roleAdmin ) {
            $attr = array('class' => 'combobox combobox-width');
            $builder->add('roles', 'choice', array(
                'choices' => $this->roles,
                'label' => 'Role(s):',
                'attr' => $attr,
                'multiple' => true,
            ));
        }

        //hook for extended class
        $this->addHookFields($builder);

        if( $this->roleAdmin ) {
            $builder->add('locked', null, array(
                'required' => false,
                'label' => 'Prevent user from logging in (lock):',
                'attr' => array('class'=>'form-control form-control-modif')
            ));
        }


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

        $params = array('read_only'=>$read_only);
        $builder->add('locations', 'collection', array(
            'type' => new LocationType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__locations__',
        ));

        $params = array('read_only'=>$read_only);
        $builder->add('employmentStatus', 'collection', array(
            'type' => new EmploymentStatusType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__employmentstatus__',
        ));



        $builder->add('credentials', new CredentialsType(), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Credentials',
            'label' => false,
            'required' => false,
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
