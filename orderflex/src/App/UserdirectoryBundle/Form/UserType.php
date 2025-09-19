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

namespace App\UserdirectoryBundle\Form;



use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User


use App\UserdirectoryBundle\Entity\UsernameType; //process.py script: replaced namespace by ::class: added use line for classname=UsernameType
use App\FellAppBundle\Form\FellowshipApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

//use App\UserdirectoryBundle\Form\PerSiteSettingsType;

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
    protected $container;
    //protected $secTokenStorage;
    //protected $secAuthChecker;
    protected $em;
    protected $hasRoleSimpleView;

    public function formConstructor( $params )
    {
        $this->params = $params;

        $this->cycle = $params['cycle'];
        $this->subjectUser = $params['user'];
        $this->cloneUser = $params['cloneuser'];
        $this->em = $params['em'];

        //$this->sc = $params['sc'];
        $this->container = $params['container'];
        //$this->secAuthChecker = $this->container->get('security.authorization_checker');
        //$this->secTokenStorage = $this->container->get('security.token_storage');

        if( !array_key_exists('showfellapp', $params) ) {
            $this->params['showfellapp'] = null;
        }

        if( array_key_exists('roles', $params) ) {
            $this->roles = $params['roles'];
        } else {
            $this->roles = null;
        }

        //echo "cycle=".$this->cycle."<br>";
        if(
            $this->container->get('user_utility')->isLoggedinUserGranted('ROLE_USERDIRECTORY_EDITOR') ||
            $this->container->get('user_utility')->isLoggedinUserGranted('ROLE_PLATFORM_DEPUTY_ADMIN')
        ) {
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

        $loggedinUser = $this->container->get('user_utility')->getLoggedinUser();
        $this->currentUser = false;
        $this->hasRoleSimpleView = false;
        if( $loggedinUser ) {
            //echo "user=" . $user->getId() . "<br>";
            //echo "subjectUser=" . $this->subjectUser->getId() . "<br>";
            if ($loggedinUser->getId() === $this->subjectUser->getId()) {
                $this->currentUser = true;
            }

            if( array_key_exists('container', $this->params) ) {
                $this->hasRoleSimpleView = $loggedinUser->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
            }
        }
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

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

        $this->addNotifyUsers($builder);

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
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\User',
            'csrf_protection' => false,
            'form_custom_value' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_user';
    }

    public function addHookFields($builder) {
        //empty
    }



    //builder add methods

    public function cloneUser($builder) {

        $options = array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
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

        $builder->add('userclone',EntityType::class,$options);

        return $builder;
    }


    public function userNamePreferredContactInfo($builder) {

        //echo "cycle=".$this->cycle."<br>";
        $readOnly = true;
        //if( $this->cycle == 'create' || $this->secAuthChecker->isGranted('ROLE_PLATFORM_ADMIN') ) {
        if( $this->cycle == 'create' ) {
            //echo "readOnly false<br>";
            $readOnly = false;
        }

        $primaryPublicUserIdAttr = array('class'=>'form-control form-control-modif');
        if( $this->cycle != 'create' ) {
            if( !$this->roleAdmin ) {
                $primaryPublicUserIdAttr['readonly'] = true;
            }
        }
        $builder->add('primaryPublicUserId', null, array(
            'label' => 'Primary Public User ID:',
            'required' => true,
            //'disabled' => $readOnly,   //($this->cycle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            //'attr' => array('class'=>'form-control', 'readonly'=>$readOnly),
            'attr' => $primaryPublicUserIdAttr
        ));

        $builder->add('avatar', DocumentType::class, array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Document',
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

//        if( !$this->subjectUser->getId() || ($this->subjectUser->getId() && $this->subjectUser->getKeytype()) ) {
//            //continue
//            //$optionsArr = array('attr' => array('class' => 'password-field form-control'));
//        } else {
//            //echo "no password";
//            //$optionsArr = array('attr' => array('class' => 'password-field form-control', 'style' => "display:none;"));
//            return;
//        }

        if( $this->cycle == "show" ) {
            return;
        }

//        if( $this->cycle != "create" ) {
//            $fieldType = 'password';
//        } else {
//            $fieldType = null;
//        }

        $builder->add('password', RepeatedType::class, array(
            //'type' => $fieldType,
            'invalid_message' => 'Please make sure the passwords match',
            'options' => array('attr' => array('class' => 'password-field form-control')),
            //'options' => $optionsArr,
            'required' => true,
            'first_options'  => array('label' => 'Password:'),
            'second_options' => array('label' => 'Repeat Password:'),
        ));
    }


    public function addUserInfos($builder) {

        $builder->add('infos', CollectionType::class, array(
            'entry_type' => UserInfoType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'label' => false,
            //'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__infos__',
        ));

        return $builder;
    }
    
    public function addNotifyUsers($builder) {
        $builder->add('notifyUsers', null, array(
            'label' => 'Only send email notifications to:',
            'multiple' => true,
            'required' => false,
            'attr' => array('class'=>'combobox')
        ));
    }


    public function addKeytype($builder,$label='Primary Public User ID Type:',$class='combobox combobox-width user-keytype-field',$defaultPrimaryPublicUserIdType=null) {
        $attr = array('class'=>$class);

        if( !$this->roleAdmin ) {
            if ($this->readonly) {
                $attr['readonly'] = true;
            }
            if ($this->cycle != 'create') {
                $attr['readonly'] = true;
            }
        }
        //echo "label=$label<br>";
        //echo $this->cycle.": attr="."<br>";
        //print_r($attr);

        $paramArr = array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
            'class' => UsernameType::class,
            //'disabled' => ($this->cycle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            'choice_label' => 'name',
            'label' => $label,
            'required' => true,
            'multiple' => false,
            'attr' => $attr,    //array('class'=>'combobox combobox-width user-keytype-field','readonly'=>$readonlyAttr ),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
            'query_builder' => function(EntityRepository $er) {
                return $this->getUserQueryBuilder($er,$this->cycle);
            }
        );

        if( $defaultPrimaryPublicUserIdType ) {
            $paramArr['data'] = $defaultPrimaryPublicUserIdType;
        }
        //echo "data=".$paramArr['data']."<br>";

        $builder->add('keytype', EntityType::class, $paramArr);
        return $builder;
    }
    //Get UsernameType:
    //default and user-added for new (create a new user)
    //default, user-added and disabled for all others (view, edit existing user)
    public function getUserQueryBuilder($er,$cycle) {
        $queryBuilder = $er->createQueryBuilder('list');
        if( $cycle == 'new' || $cycle == 'create' ) {
            $queryBuilder
                ->where("list.type = :typedef OR list.type = :typeadd")
                ->orderBy("list.orderinlist", "ASC")
                ->setParameters(array(
                    'typedef' => 'default',
                    'typeadd' => 'user-added',
                ));
        } else {
            $queryBuilder
                ->where("list.type = :typedef OR list.type = :typeadd OR list.type = :typedisabled")
                ->orderBy("list.orderinlist", "ASC")
                ->setParameters(array(
                    'typedef' => 'default',
                    'typeadd' => 'user-added',
                    'typedisabled' => 'disabled'
                ));
        }
        return $queryBuilder;
    }


    public function globalUserPreferences($builder) {

        if( !$this->hasRoleSimpleView ) {
            $builder->add('preferences', UserPreferencesType::class, array(
                'form_custom_value' => $this->params,
                'data_class' => 'App\UserdirectoryBundle\Entity\UserPreferences',
                'label' => false,
                'required' => false,
            ));
        }

        //Roles
        if( $this->roles && ($this->cycle == "show" || $this->roleAdmin) ) {
            $attr = array('class' => 'combobox combobox-width');
            $builder->add('roles', ChoiceType::class, array( //flipped
                'choices' => $this->roles,
                //'choices_as_values' => true,
                'invalid_message' => 'invalid value: user roles',
                'label' => 'Role(s):',
                'attr' => $attr,
                'multiple' => true,
            ));

            if( !$this->hasRoleSimpleView ) {
                //permissions: show list of
                $builder->add('permissions', CollectionType::class, array(
                    'entry_type' => PermissionType::class,
                    'label' => false,
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'prototype_name' => '__permissions__',
                ));
            }
        }


        if( $this->roleAdmin ) {
            if( !$this->hasRoleSimpleView ) {
                $builder->add('locked', CheckboxType::class, array(
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
        if( !$this->hasRoleSimpleView ) {
            $builder->add('perSiteSettings', PerSiteSettingsType::class, array(
                'form_custom_value_user' => null,
                'form_custom_value_roleAdmin' => $this->roleAdmin,
                'form_custom_value' => $this->params,
                'data_class' => 'App\UserdirectoryBundle\Entity\PerSiteSettings',
                'label' => false,
                'required' => false,
            ));
        }
    }

    public function titlesSections($builder) {
        //Administrative Titles
        $params = array('disabled'=>$this->readonly,'label'=>'Administrative','fullClassName'=>'App\UserdirectoryBundle\Entity\AdministrativeTitle','formname'=>'administrativetitletype','cycle'=>$this->cycle);
        $params = array_merge($this->params, $params);
        //BaseTitleType($params)
        $builder->add('administrativeTitles', CollectionType::class, array(
            'entry_type' => AdministrativeTitleType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__administrativetitles__',
        ));

        $params = array('disabled'=>$this->readonly,'label'=>'Academic Appointment','fullClassName'=>'App\UserdirectoryBundle\Entity\AppointmentTitle','formname'=>'appointmenttitletype','cycle'=>$this->cycle);
        $params = array_merge($this->params, $params);
        $builder->add('appointmentTitles', CollectionType::class, array(
            'entry_type' => AppointmentTitleType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__appointmenttitles__',
        ));

        $params = array('disabled'=>$this->readonly,'label'=>'Medical Appointment','fullClassName'=>'App\UserdirectoryBundle\Entity\MedicalTitle','formname'=>'medicaltitletype','cycle'=>$this->cycle);
        $params = array_merge($this->params, $params);
        $builder->add('medicalTitles', CollectionType::class, array(
            'entry_type' => MedicalTitleType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
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
        $params = array('disabled'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser,'container'=>$this->container);
        $builder->add('trainings', CollectionType::class, array(
            'entry_type' => TrainingType::class,
            'entry_options' => array(
                'form_custom_value' => $params,
                'form_custom_value_entity' => null
            ),
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
        $params = array(
            'disabled'=>$this->readonly,
            'admin'=>$this->roleAdmin,
            'currentUser'=>$this->currentUser,
            'cycle'=>$this->cycle,
            'em'=>$this->em,
            'subjectUser'=>$this->subjectUser,
            'container'=>$this->container
        );
        $builder->add('locations', CollectionType::class, array(
            'entry_type' => LocationType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
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
            $params = array('em'=>$this->params['em'],'disabled'=>$this->readonly,'currentUser'=>$this->currentUser,'admin'=>$this->roleAdmin);
            $builder->add('employmentStatus', CollectionType::class, array(
                'entry_type' => EmploymentStatusType::class,
                'entry_options' => array(
                    'form_custom_value' => $params
                ),
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
        $params = array('disabled'=>$this->readonly,'admin'=>$this->roleAdmin,'subjectUser'=>$this->subjectUser,'cycle'=>$this->cycle,'em'=>$this->em,'container'=>$this->container);
        $builder->add('researchLabs', CollectionType::class, array(
            'entry_type' => ResearchLabType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__researchlabs__',
        ));
}
        if( !$this->hasRoleSimpleView ) {
            //it takes 7 seconds to load
            $params = array('disabled'=>$this->readonly,'admin'=>$this->roleAdmin,'subjectUser'=>$this->subjectUser,'cycle'=>$this->cycle,'em'=>$this->em);
            $builder->add('grants', CollectionType::class, array(
                'entry_type' => GrantType::class,
                'entry_options' => array(
                    'form_custom_value' => $params
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__grants__',
            ));

            $params = array('disabled'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
            $builder->add('publications', CollectionType::class, array(
                'entry_type' => PublicationType::class,
                'entry_options' => array(
                    'form_custom_value' => $params
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__publications__',
            ));

            $params = array('disabled'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
            $builder->add('books', CollectionType::class, array(
                'entry_type' => BookType::class,
                'entry_options' => array(
                    'form_custom_value' => $params
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__books__',
            ));

            $params = array('disabled'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
            $builder->add('lectures', CollectionType::class, array(
                'entry_type' => LectureType::class,
                'entry_options' => array(
                    'form_custom_value' => $params
                ),
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
            $params = array('container'=>$this->container,'em'=>$this->em,'cycle'=>$this->cycle,'roleAdmin'=>$this->roleAdmin);
            $builder->add('credentials', CredentialsType::class, array(
                'form_custom_value' => $params,
                'data_class' => 'App\UserdirectoryBundle\Entity\Credentials',
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

        $params = array('disabled'=>$readOnlyComment,'label'=>'Public','fullClassName'=>'App\UserdirectoryBundle\Entity\PublicComment','formname'=>'publiccomments','em'=>$this->params['em']);
        $builder->add('publicComments', CollectionType::class, array(
            'entry_type' => PublicCommentType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__publiccomments__',
        ));

        if( $this->roleAdmin || $this->currentUser ) {
            $params = array('roleAdmin'=>$this->roleAdmin,'disabled'=>$readOnlyComment,'label'=>'Private','fullClassName'=>'App\UserdirectoryBundle\Entity\PrivateComment','formname'=>'privatecomments','em'=>$this->params['em']);
            $builder->add('privateComments', CollectionType::class, array(
                'entry_type' => PrivateCommentType::class,
                'entry_options' => array(
                    'form_custom_value' => $params
                ),
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
            $params = array('disabled'=>$this->readonly,'label'=>'Administrative','fullClassName'=>'App\UserdirectoryBundle\Entity\AdminComment','formname'=>'admincomments','em'=>$this->params['em']);
            $builder->add('adminComments', CollectionType::class, array(
                'entry_type' => AdminCommentType::class,
                'entry_options' => array(
                    'form_custom_value' => $params
                ),
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
            $params = array('disabled'=>$this->readonly,'label'=>'Confidential','fullClassName'=>'App\UserdirectoryBundle\Entity\ConfidentialComment','formname'=>'confidentialcomments','em'=>$this->params['em']);
            $builder->add('confidentialComments', CollectionType::class, array(
                'entry_type' => ConfidentialCommentType::class,
                'entry_options' => array(
                    'form_custom_value' => $params
                ),
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
        //testing: why fellowshipApplications should be shown in the user profile page?
        return $builder;
        //exit('addFellowshipApplication to user profile not implemented');

        $builder->add('fellowshipApplications', CollectionType::class, array(
            'entry_type' => FellowshipApplicationType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
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
