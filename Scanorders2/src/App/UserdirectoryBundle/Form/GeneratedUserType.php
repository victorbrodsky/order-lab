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

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\PerSiteSettingsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class GeneratedUserType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('author', null, array(
            'label' => "Created By:",
            'multiple' => false,
            'disabled' => true,
            'attr' => array('class' => 'combobox'),
        ));

        $builder->add('createDate', DateType::class, array(
            'label' => "Created On:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'disabled' => true,
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('locked', CheckboxType::class, array(
            'required' => false,
            'label' => 'Prevent user from logging in (lock):',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('roles', ChoiceType::class, array(
            'choices' => $this->params['roles'],
            //'choices_as_values' => true,
            'label' => ucfirst($this->params['sitename']) . ' Role(s):',
            'attr' => array('class' => 'combobox'),
            'multiple' => true,
        ));

        $builder->add('emailNotification',CheckboxType::class, array(
            'label' => 'Inform authorized user by email:',
            'mapped' => false,
            'required' => false,
            'data' => true,
            'attr' => array('class' => 'form-control'),
        ));

//        $builder->add('password', RepeatedType::class, array(
//            'invalid_message' => 'Please make sure the passwords match',
//            'options' => array('attr' => array('class' => 'password-field form-control')),
//            'required' => true,
//            'first_options'  => array('label' => 'Password:'),
//            'second_options' => array('label' => 'Repeat Password:'),
//        ));

        $builder->add('infos', CollectionType::class, array(
            'entry_type' => UserInfoType::class,
            'label' => false,
            //'required' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__infos__',
        ));


        $builder->add('primaryPublicUserId', null, array(
            'label' => 'Primary Public User ID:',
            //'disabled' => $readOnly,   //($this->cycle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            'attr' => array('class'=>'form-control'), //'readonly'=>$readOnly
        ));

        $builder->add('keytype', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:UsernameType',
            //'disabled' => ($this->cycle == 'create' ? false : true ), //it is not possible to edit keytype for existed user
            'choice_label' => 'name',
            'label' => "Primary Public User ID Type:",
            'required' => true,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'), //user-keytype-field 'readonly'=>false
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

        $builder->add('otherUserParam', null, array(
            'label' => 'Optional Parameter:',
            'disabled' => true,
            'attr' => array('class'=>'form-control'),
        ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\User',
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_user';
    }



}
