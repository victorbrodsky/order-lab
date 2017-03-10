<?php

/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Oleg\UserdirectoryBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

//use Oleg\UserdirectoryBundle\Form\PerSiteSettingsType;

class UserType extends AbstractType
{

    protected $params;
    protected $cycle;
    protected $readonly;
    protected $roleAdmin;
    protected $subjectUser;
    protected $currentUser;
    protected $cloneUser;
    protected $roles;
    protected $sc;
    protected $em;
    protected $hasRoleSimpleView;

    public function __construct( $params )
    {


        $this->params = $params;

        $this->cycle = $params['cycle'];
        $this->subjectUser = $params['user'];
        $this->cloneUser = $params['cloneuser'];
        $this->sc = $params['sc'];
        $this->em = $params['em'];

        if( !array_key_exists('showfellapp', $params) ) {
            $this->params['showfellapp'] = null;
        }

        if( array_key_exists('roles', $params) ) {
            $this->roles = $params['roles'];
        } else {
            $this->roles = null;
        }

        //echo "cycle=".$this->cycle."<br>";
        if( $this->sc->isGranted('ROLE_USERDIRECTORY_EDITOR') || $this->sc->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            //echo "role ADMIN<br>";
            $this->roleAdmin = true;
        } else {
            //echo "role not ADMIN<br>";
            $this->roleAdmin = false;

        }
        //echo "0 roleAdmin=".$this->roleAdmin."<br>";

        $this->readonly = false;
        //$readonlyAttr = 'false';
        if( !$this->roleAdmin ) {
            $this->readonly = true;
            //$readonlyAttr = 'true';
        }

        $this->currentUser = false;
        $user = $this->sc->getToken()->getUser();
        if( $user->getId() === $this->subjectUser->getId() ) {
            $this->currentUser = true;
        }

        $this->hasRoleSimpleView = false;
        if( array_key_exists('sc', $this->params) ) {
            $this->hasRoleSimpleView = $this->params['sc']->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_EDITOR_SIMPLEVIEW");
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //hook for extended class
        $this->addHookFields($builder);

        //dummy user clone field
        if( $this->cycle == "create" ) {
            $this->cloneUser($builder);
        }

        //keytype
        $this->addKeytype($builder);

        //Name and Preferred Contact Info
        $this->userNamePreferredContactInfo($builder);

        $this->userPassword($builder);

        $this->addUserInfos($builder);

        //Global User Preferences
        $this->globalUserPreferences($builder);

        $this->addPerSiteSettings($builder);

        //Titles
        $this->titlesSections($builder);

        $this->userTrainings($builder);

        $this->userLocations($builder);

        //visible only to admin or user himself on view
        $this->employmentStatus($builder);

        $this->researchUser($builder);

        $this->addCredentials($builder);

        $this->addComments($builder);

        if( $this->params['showfellapp'] ) {
            $this->addFellowshipApplication($builder);
        }


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'csrf_protection' => false,
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



    //builder add methods

    public function cloneUser($builder) {

        $options = array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => "Clone:",
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width user-userclone-field'),
            'required' => false,
            'mapped' => false,
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->where("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                        ->orderBy("user.primaryPublicUserId","ASC");
                },
        );

        //if( $this->subjectUser->getPrimaryPublicUserId() && $this->subjectUser->getPrimaryPublicUserId() != "" ) {
        if( $this->cloneUser ) {
            $options['data'] = $this->cloneUser;
        }

        $builder->add('userclone','entity',$options);

        return $builder;
    }


    public function userNamePreferredContactInfo($builder) {
        
        $readOnly = true;
        if( $this->cycle == 'create' || $this->sc->isGranted('ROLE_PLATFORM_ADMIN') ) {
            $readOnly = false;
        }
        
        $builder->add('primaryPublicUserId', null, array(
            'label' => '* Primary Public User ID:',
            'read_only' => $readOnly,   //($this->cycle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('avatar', new DocumentType(), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Document',
            'label' => false
        ));

        return $builder;
    }

    public function userPassword($builder) {

        //show password only for a new user or for an existing user with keytype 'local-user'
        if( !$this->subjectUser->getId() || ($this->subjectUser->getId() && $this->subjectUser->getKeytype() && $this->subjectUser->getKeytype()->getAbbreviation() == 'local-user') ) {
            //continue
        } else {
            //echo "no password";
            return;
        }

        if( $this->cycle == "show" ) {
            return;
        }

//        if( $this->cycle != "create" ) {
//            $fieldType = 'password';
//        } else {
//            $fieldType = null;
//        }

        $builder->add('password', 'repeated', array(
            //'type' => $fieldType,
            'invalid_message' => 'Please make sure the passwords match',
            'options' => array('attr' => array('class' => 'password-field form-control')),
            'required' => true,
            'first_options'  => array('label' => 'Password:'),
            'second_options' => array('label' => 'Repeat Password:'),
        ));
    }


    public function addUserInfos($builder) {

        $builder->add('infos', 'collection', array(
            'type' => new UserInfoType(),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__infos__',
        ));

        return $builder;
    }



    public function addKeytype($builder,$label='* Primary Public User ID Type:',$class='combobox combobox-width user-keytype-field') {
        $attr = array('class'=>$class);
        if( $this->readonly ) {
            $attr['readonly'] = 'readonly';
        }
        $builder->add('keytype', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:UsernameType',
            'read_only' => ($this->cycle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            'property' => 'name',
            'label' => $label,
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
        return $builder;
    }


    public function globalUserPreferences($builder) {

        if( !$this->hasRoleSimpleView ) {
            $builder->add('preferences', new UserPreferencesType($this->params), array(
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserPreferences',
                'label' => false,
                'required' => false,
            ));
        }

        //Roles
        if( $this->roles && ($this->cycle == "show" || $this->roleAdmin) ) {
            $attr = array('class' => 'combobox combobox-width');
            $builder->add('roles', 'choice', array(
                'choices' => $this->roles,
                'label' => 'Role(s):',
                'attr' => $attr,
                'multiple' => true,
            ));

            //permissions: show list of
            $builder->add('permissions', 'collection', array(
                'type' => new PermissionType($this->params),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__permissions__',
            ));
        }


        if( $this->roleAdmin ) {
            if( !$this->hasRoleSimpleView ) {
                $builder->add('locked', null, array(
                    'required' => false,
                    'label' => 'Prevent user from logging in (lock):',
                    'attr' => array('class'=>'form-control form-control-modif')
                ));

                $builder->add('testingAccount', null, array(
                    'required' => false,
                    'label' => 'This is an account for testing purposes (hide on live site):',
                    'attr' => array('class' => 'form-control form-control-modif')
                ));
            }
        }

        return $builder;
    }

    public function addPerSiteSettings($builder) {
        $builder->add('perSiteSettings', new PerSiteSettingsType(null, $this->roleAdmin, $this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\PerSiteSettings',
            'label' => false,
            'required' => false,
        ));
    }

    public function titlesSections($builder) {
        //Administrative Titles
        $params = array('read_only'=>$this->readonly,'label'=>'Administrative','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdministrativeTitle','formname'=>'administrativetitletype','cycle'=>$this->cycle);
        $params = array_merge($this->params, $params);
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

        $params = array('read_only'=>$this->readonly,'label'=>'Academic Appointment','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AppointmentTitle','formname'=>'appointmenttitletype','cycle'=>$this->cycle);
        $params = array_merge($this->params, $params);
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

        $params = array('read_only'=>$this->readonly,'label'=>'Medical Appointment','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\MedicalTitle','formname'=>'medicaltitletype','cycle'=>$this->cycle);
        $params = array_merge($this->params, $params);
        $builder->add('medicalTitles', 'collection', array(
            'type' => new BaseTitleType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__medicaltitles__',
        ));

        return $builder;
    }

    public function userTrainings($builder) {
        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser,'sc'=>$this->sc);
        $builder->add('trainings', 'collection', array(
            'type' => new TrainingType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__trainings__',
        ));

        return $builder;
    }

    public function userLocations($builder) {
        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser,'sc'=>$this->sc);
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

        return $builder;
    }

    public function employmentStatus($builder) {
        if( $this->roleAdmin || ($this->currentUser == false && $this->cycle == "show") ) {
            $params = array('em'=>$this->params['em'],'read_only'=>$this->readonly,'currentUser'=>$this->currentUser,'admin'=>$this->roleAdmin);
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

        return $builder;
    }

    public function researchUser($builder) {
        
if(1){    
        //it takes 4 seconds to load
        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'subjectUser'=>$this->subjectUser,'cycle'=>$this->cycle,'em'=>$this->em,'sc'=>$this->sc);
        $builder->add('researchLabs', 'collection', array(
            'type' => new ResearchLabType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__researchlabs__',
        ));
}
if(1){ 
        //it takes 7 seconds to load
        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'subjectUser'=>$this->subjectUser,'cycle'=>$this->cycle,'em'=>$this->em);
        $builder->add('grants', 'collection', array(
            'type' => new GrantType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__grants__',
        ));
}

if(1){        
        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
        $builder->add('publications', 'collection', array(
            'type' => new PublicationType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__publications__',
        ));

        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
        $builder->add('books', 'collection', array(
            'type' => new BookType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__books__',
        ));

        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
        $builder->add('lectures', 'collection', array(
            'type' => new LectureType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__lectures__',
        ));
}

        return $builder;
    }

    public function addCredentials($builder) {
        if( $this->roleAdmin || $this->currentUser ) {
            $params = array('sc'=>$this->sc,'em'=>$this->em,'cycle'=>$this->cycle,'roleAdmin'=>$this->roleAdmin);
            $builder->add('credentials', new CredentialsType($params), array(
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\Credentials',
                'label' => false,
                'required' => false,
            ));
        }

        return $builder;
    }

    public function addComments($builder) {
        $readOnlyComment = true;
        if( $this->currentUser || $this->readonly == false ) {
            $readOnlyComment = false;
        }

        $params = array('read_only'=>$readOnlyComment,'label'=>'Public','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\PublicComment','formname'=>'publiccomments','em'=>$this->params['em']);
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

        if( $this->roleAdmin || $this->currentUser ) {
            $params = array('roleAdmin'=>$this->roleAdmin,'read_only'=>$readOnlyComment,'label'=>'Private','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\PrivateComment','formname'=>'privatecomments','em'=>$this->params['em']);
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
            $params = array('read_only'=>$this->readonly,'label'=>'Administrative','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdminComment','formname'=>'admincomments','em'=>$this->params['em']);
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

        if( $this->roleAdmin || ($this->currentUser && $this->cycle == 'show') ) {
            $params = array('read_only'=>$this->readonly,'label'=>'Confidential','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\ConfidentialComment','formname'=>'confidentialcomments','em'=>$this->params['em']);
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

        return $builder;
    }


    public function addFellowshipApplication($builder) {
        $builder->add('fellowshipApplications', 'collection', array(
            'type' => new FellowshipApplicationType($this->params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__fellowshipapplications__',
        ));

        return $builder;
    }
}
