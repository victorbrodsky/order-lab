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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class ResetPasswordType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add('password', RepeatedType::class, array(
            'invalid_message' => 'Please make sure the passwords you typed match',
            'options' => array('attr' => array('class' => 'password-field form-control')),
            'type' => PasswordType::class,
            'first_options'  => array('label' => 'New password:'),
            'second_options' => array('label' => 'Re-type new password:'),
        ));

        $builder->add('reset', SubmitType::class, array(
            'label' => 'Reset Password',
            'attr' => array('class' => 'btn btn-primary')
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    //public function configureOptions(OptionsResolver $resolver)
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\User',
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

}
