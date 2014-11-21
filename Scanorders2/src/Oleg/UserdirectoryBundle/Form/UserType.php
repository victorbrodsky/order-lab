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
    protected $subjectUser;
    protected $cloneUser;
    protected $roles;
    protected $sc;
    protected $em;

    public function __construct( $params )
    {
        $this->cicle = $params['cicle'];
        $this->subjectUser = $params['user'];
        $this->cloneUser = $params['cloneuser'];
        $this->roles = $params['roles'];
        $this->sc = $params['sc'];
        $this->em = $params['em'];

        //echo "cicle=".$cicle."<br>";
        if( $this->cicle == 'create' ) {
            $this->roleAdmin = $this->sc->isGranted('ROLE_USERDIRECTORY_EDITOR');
        } else {
            $this->roleAdmin = $this->sc->isGranted('ROLE_ADMIN');
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $currentUser = false;
        $user = $this->sc->getToken()->getUser();
        if( $user->getId() === $this->subjectUser->getId() ) {
            $currentUser = true;
        }

        $read_only = false;
        //$readonlyAttr = 'false';
        if( !$this->roleAdmin ) {
            $read_only = true;
            //$readonlyAttr = 'true';
        }
        //echo "read_only=".$read_only."<br>";


        //dummy user clone field
        if( $this->cicle == "create" ) {

            $options = array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Clone:",
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width user-userclone-field'),
                'required' => false,
                'mapped' => false,
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('user')
                            ->where("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                            ->orderBy("user.primaryPublicUserId","ASC");
                    },
            );

            //if( $this->subjectUser->getPrimaryPublicUserId() && $this->subjectUser->getPrimaryPublicUserId() != "" ) {
            if( $this->cloneUser ) {
                $options['data'] = $this->cloneUser;
            }

            $builder->add('userclone','entity',$options);
        }

        $attr = array('class'=>'combobox combobox-width user-keytype-field');
        if( $read_only ) {
            $attr['readonly'] = 'readonly';
        }
        $builder->add('keytype', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:UsernameType',
            'read_only' => ($this->cicle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            'property' => 'name',
            'label' => '* Primary Public User ID Type:',
            'required' => true,
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
            'label' => '* Primary Public User ID:',
            'read_only' => ($this->cicle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('firstName', null, array(
            'label' => '* First Name:',
            'attr' => array('class'=>'form-control form-control-modif') //'required'=>'required'
        ));
        $builder->add('middleName', null, array(
            'label' => 'Middle Name:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('lastName', null, array(
            'label' => '* Last Name:',
            'attr' => array('class'=>'form-control form-control-modif') //'required'=>'required'
        ));
        $builder->add('email', 'email', array(
            'label' => 'Preferred Email:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('displayName', null, array(
            'label' => 'Preferred Full Name for Display:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));
        $builder->add('preferredPhone', null, array(
            'label' => 'Preferred Phone Number:',
            'attr' => array('class'=>'form-control form-control-modif phone-mask')
        ));
        $builder->add('initials', null, array(
            'label' => 'Abbreviated name/Initials used by lab staff for deliveries:',
            'attr' => array('class'=>'form-control form-control-modif')
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
        $params = array('read_only'=>$read_only,'label'=>'Administrative','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdministrativeTitle','formname'=>'administrativetitletype','cicle'=>$this->cicle);
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

        $params = array('read_only'=>$read_only,'label'=>'Academic Appointment','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AppointmentTitle','formname'=>'appointmenttitletype','cicle'=>$this->cicle);
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

        $params = array('read_only'=>$read_only,'admin'=>$this->roleAdmin,'currentUser'=>$currentUser,'cicle'=>$this->cicle,'em'=>$this->em);
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

        if( $this->roleAdmin || ($currentUser && $this->cicle == "show") ) {
            $params = array('read_only'=>$read_only,'currentUser'=>$currentUser,'admin'=>$this->roleAdmin);
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
        }

        $builder->add('researchLabs', 'collection', array(
            'type' => new ResearchLabType(null),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__researchlabs__',
        ));

        if( $this->roleAdmin || $currentUser ) {
            $params = array('em'=>$this->em);
            $builder->add('credentials', new CredentialsType($params), array(
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\Credentials',
                'label' => false,
                'required' => false,
            ));
        }

        $readOnlyComment = true;
        if( $currentUser || $read_only == false ) {
            $readOnlyComment = false;
        }

        $params = array('read_only'=>$readOnlyComment,'label'=>'Public','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\PublicComment','formname'=>'publiccomments');
        $builder->add('publicComments', 'collection', array(
            'type' => new BaseCommentsType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__publiccomments__',
        ));

        if( $this->roleAdmin || $currentUser ) {
            $params = array('read_only'=>$readOnlyComment,'label'=>'Private','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\PrivateComment','formname'=>'privatecomments');
            $builder->add('privateComments', 'collection', array(
                'type' => new BaseCommentsType($params),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__privatecomments__',
            ));
        }

        if( $this->roleAdmin ) {
            $params = array('read_only'=>$read_only,'label'=>'Administrative','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdminComment','formname'=>'admincomments');
            $builder->add('adminComments', 'collection', array(
                'type' => new BaseCommentsType($params),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__admincomments__',
            ));
        }

        if( $this->roleAdmin || ($currentUser && $this->cicle == 'show') ) {
            $params = array('read_only'=>$read_only,'label'=>'Confidential','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\ConfidentialComment','formname'=>'confidentialcomments');
            $builder->add('confidentialComments', 'collection', array(
                'type' => new BaseCommentsType($params),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__confidentialcomments__',
            ));
        }

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
