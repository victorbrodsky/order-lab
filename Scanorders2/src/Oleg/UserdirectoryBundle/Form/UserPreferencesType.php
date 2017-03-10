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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Util\TimeZoneUtil;

class UserPreferencesType extends AbstractType
{

    protected $params;
//    protected $cycle;
//    protected $roleAdmin;
//    protected $user;
    protected $roles;

    public function __construct($params)
    {
        $this->params = $params;
//        $this->cycle = $cycle;
//        $this->user = $user;
//        $this->roleAdmin = $roleAdmin;
//        $this->roles = $roles;

        if( array_key_exists('roles', $params) ) {
            $this->roles = $params['roles'];
        } else {
            $this->roles = null;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $hasRoleSimpleView = false;
        if( array_key_exists('sc', $this->params) ) {
            $hasRoleSimpleView = $this->params['sc']->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_EDITOR_SIMPLEVIEW");
        }


        if( !$hasRoleSimpleView ) {

            //timezone
            $tzUtil = new TimeZoneUtil();

            $builder->add('timezone', 'choice', array(
                'label' => 'Time Zone:',
                //'label' => $translator->translate('timezone',$formtype,'Time Zone:'),
                'choices' => $tzUtil->tz_list(),
                'required' => true,
                'preferred_choices' => array('America/New_York'),
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('languages', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:LanguageList',
                'label' => "Language(s):",
                'required' => false,
                'multiple' => true,
                'property' => 'fulltitle',
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

            $builder->add('locale', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:LocaleList',
                'label' => "Locale:",
                'required' => false,
                'multiple' => false,
                'property' => 'fulltitle',
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

            $builder->add( 'showToInstitutions', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:Institution',
                //'property' => 'name',
                'property' => 'getTreeName',
                'label'=>'Only show this profile to members of the following institution(s):',
                'required'=> false,
                'multiple' => true,
                //'empty_value' => false,
                'attr' => array('class' => 'combobox combobox-width user-preferences-showToInstitutions'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("(list.type = :typedef OR list.type = :typeadd) AND list.level = :level")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                            'level' => 0
                        ));
                },
            ));

            $builder->add('showToRoles', 'choice', array(
                'choices' => $this->roles,
                'label' => 'Only show this profile to users with the following roles:',
                'attr' => array('class' => 'combobox combobox-width user-preferences-showToRoles'),
                'multiple' => true,
            ));

            $builder->add('excludeFromSearch', 'checkbox', array(
                'required' => false,
                'label' => 'Exclude from Employee Directory search results:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

            $builder->add('noAttendingEmail', 'checkbox', array(
                'required' => false,
                'label' => 'Do not send a notification email if listed as an "attending" in a Call Log Book Entry:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

            $builder->add('lifeForm', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:LifeFormList',
                'property' => 'name',
                'label' => "Life Form:",
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added'
                        ));
                },
            ));

            $builder->add('hide', 'checkbox', array(
                'required' => false,
                'label' => 'Hide this profile:',
                'attr' => array('class'=>'form-control form-control-modif user-preferences-hide', 'style'=>'margin:0')
            ));
        }


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserPreferences'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_userdirectorybundle_userpreferences';
    }

}
