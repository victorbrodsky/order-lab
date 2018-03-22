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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

//use Oleg\UserdirectoryBundle\Form\PerSiteSettingsType;

class UserSimpleType extends AbstractType
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
    protected $secTokenStorage;
    protected $secAuthChecker;
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
        $this->secAuthChecker = $this->container->get('security.authorization_checker');
        $this->secTokenStorage = $this->container->get('security.token_storage');

        if( !array_key_exists('showfellapp', $params) ) {
            $this->params['showfellapp'] = null;
        }

        if( array_key_exists('roles', $params) ) {
            $this->roles = $params['roles'];
        } else {
            $this->roles = null;
        }

        //echo "cycle=".$this->cycle."<br>";
        if( $this->secAuthChecker->isGranted('ROLE_USERDIRECTORY_EDITOR') || $this->secAuthChecker->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
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
        $user = $this->secTokenStorage->getToken()->getUser();
        if( $user->getId() === $this->subjectUser->getId() ) {
            $this->currentUser = true;
        }

        $this->hasRoleSimpleView = false;
        if( array_key_exists('container', $this->params) ) {
            $this->hasRoleSimpleView = $this->params['container']->get('security.token_storage')->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $this->userNamePreferredContactInfo($builder);

        $this->addUserInfos($builder);

        $this->titlesSections($builder);

    }

    /**
     * @param OptionsResolver $resolver
     */
    //public function configureOptions(OptionsResolver $resolver)
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'csrf_protection' => false,
            'form_custom_value' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_user';
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
            $primaryPublicUserIdAttr['readonly'] = true;
        }
        $builder->add('primaryPublicUserId', null, array(
            'label' => 'CWID:',
            'required' => false,
            //'disabled' => $readOnly,   //($this->cycle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            //'attr' => array('class'=>'form-control', 'readonly'=>$readOnly),
            'attr' => $primaryPublicUserIdAttr
        ));


        return $builder;
    }

    public function addUserInfos($builder) {

        $builder->add('infos', CollectionType::class, array(
            'entry_type' => UserInfoType::class,
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

    public function titlesSections($builder) {
        //Administrative Titles
        $params = array('disabled'=>$this->readonly,'label'=>'Administrative','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdministrativeTitle','formname'=>'administrativetitletype','cycle'=>$this->cycle);
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

//        $params = array('disabled'=>$this->readonly,'label'=>'Academic Appointment','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AppointmentTitle','formname'=>'appointmenttitletype','cycle'=>$this->cycle);
//        $params = array_merge($this->params, $params);
//        $builder->add('appointmentTitles', CollectionType::class, array(
//            'entry_type' => AppointmentTitleType::class,
//            'entry_options' => array(
//                'form_custom_value' => $params
//            ),
//            'label' => false,
//            'required' => false,
//            'allow_add' => true,
//            'allow_delete' => true,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__appointmenttitles__',
//        ));
//
//        $params = array('disabled'=>$this->readonly,'label'=>'Medical Appointment','fullClassName'=>'Oleg\UserdirectoryBundle\Entity\MedicalTitle','formname'=>'medicaltitletype','cycle'=>$this->cycle);
//        $params = array_merge($this->params, $params);
//        $builder->add('medicalTitles', CollectionType::class, array(
//            'entry_type' => MedicalTitleType::class,
//            'entry_options' => array(
//                'form_custom_value' => $params
//            ),
//            'label' => false,
//            'required' => false,
//            'allow_add' => true,
//            'allow_delete' => true,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__medicaltitles__',
//        ));

        return $builder;
    }

}
