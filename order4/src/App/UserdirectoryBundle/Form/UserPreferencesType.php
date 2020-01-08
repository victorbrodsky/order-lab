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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use App\UserdirectoryBundle\Util\TimeZoneUtil;

class UserPreferencesType extends AbstractType
{

    protected $params;
//    protected $cycle;
//    protected $roleAdmin;
//    protected $user;
    protected $roles;

    public function formConstructor($params)
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

        $this->formConstructor($options['form_custom_value']);

        $hasRoleSimpleView = false;
        if( array_key_exists('container', $this->params) ) {
            $hasRoleSimpleView = $this->params['container']->get('security.token_storage')->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
        }


        if( !$hasRoleSimpleView ) {

            //timezone
            $tzUtil = new TimeZoneUtil();

            $builder->add('timezone', ChoiceType::class, array( //flipped
                'label' => 'Time Zone:',
                //'label' => $translator->translate('timezone',$formtype,'Time Zone:'),
                'choices' => $tzUtil->tz_list(),
                //'choices_as_values' => true,
                'invalid_message' => 'invalid value: user prefer timezone',
                'required' => true,
                'preferred_choices' => array('America/New_York'),
                'attr' => array('class' => 'combobox combobox-width')
            ));

            $builder->add('languages', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:LanguageList',
                'label' => "Language(s):",
                'required' => false,
                'multiple' => true,
                'choice_label' => 'fulltitle',
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

            $builder->add('locale', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:LocaleList',
                'label' => "Locale:",
                'required' => false,
                'multiple' => false,
                'choice_label' => 'fulltitle',
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

            $builder->add( 'showToInstitutions', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:Institution',
                //'choice_label' => 'name',
                'choice_label' => 'getTreeName',
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

            $builder->add('showToRoles', ChoiceType::class, array( //flipped
                'choices' => $this->roles,
                //'choices_as_values' => true,
                'invalid_message' => 'invalid value: showToRoles',
                'label' => 'Only show this profile to users with the following roles:',
                'attr' => array('class' => 'combobox combobox-width user-preferences-showToRoles'),
                'multiple' => true,
            ));

            $builder->add('excludeFromSearch', CheckboxType::class, array(
                'required' => false,
                'label' => 'Exclude from Employee Directory search results:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

            $builder->add('noAttendingEmail', CheckboxType::class, array(
                'required' => false,
                'label' => 'Do not send a notification email if listed as an "attending" in a Call Log Book Entry:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

            $builder->add('lifeForm', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:LifeFormList',
                'choice_label' => 'name',
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

            $builder->add('hide', CheckboxType::class, array(
                'required' => false,
                'label' => 'Hide this profile:',
                'attr' => array('class'=>'form-control form-control-modif user-preferences-hide', 'style'=>'margin:0')
            ));
        }


    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\UserPreferences',
            'form_custom_value' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_userpreferences';
    }

}
