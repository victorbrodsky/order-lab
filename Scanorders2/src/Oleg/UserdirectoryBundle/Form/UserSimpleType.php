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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $this->readonly = false;
        $this->hasRoleSimpleView = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        //form start

        //show it on the "Add New" user modal
        if( !isset($this->params['hidePrimaryPublicUserId']) ) {

            //add user primaryPublicId (keytype) to the add new modal
            //$this->addKeytype($builder);

            $this->userNamePreferredContactInfo($builder);
        }

        $this->addUserInfos($builder);

        $this->titlesSections($builder);

        if( isset($this->params['activateBtn']) ) {
            $builder->add('activate', SubmitType::class, array(
                'label' => 'Activate Account',
                'attr' => array('class' => 'btn btn-primary') //'onclick'=>'transresValidateHandsonTable();'
            ));
        }
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

        $primaryPublicUserIdLabel = 'User Name';
        if( isset($this->params['primaryPublicUserIdLabel']) ) {
            $primaryPublicUserIdLabel = $this->params['primaryPublicUserIdLabel'];
        }

        $primaryPublicUserIdAttr = array('class'=>'form-control form-control-modif');
        $builder->add('primaryPublicUserId', null, array(
            'label' => $primaryPublicUserIdLabel.":",
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
        $params = array(
            'disabled'=>$this->readonly,
            'label'=>'Administrative',
            'fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdministrativeTitle',
            'formname'=>'administrativetitletype',
            'cycle'=>$this->cycle
        );
        $params = array_merge($this->params, $params);
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

        return $builder;
    }


    public function addKeytype($builder,$label='Authentication (to enable log in):',$class='combobox user-keytype-field',$defaultPrimaryPublicUserIdType=null) {
        $attr = array('class'=>$class);

        $paramArr = array(
            'class' => 'OlegUserdirectoryBundle:UsernameType',
            'choice_label' => 'name',
            'label' => $label,
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
        );

        if( $defaultPrimaryPublicUserIdType ) {
            $paramArr['data'] = $defaultPrimaryPublicUserIdType;
        }
        //echo "data=".$paramArr['data']."<br>";

        $builder->add('keytype', EntityType::class, $paramArr);
        return $builder;
    }

}
