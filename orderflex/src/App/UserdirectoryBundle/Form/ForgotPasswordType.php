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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class ForgotPasswordType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add( 'email', EmailType::class, array(
            'label'=>'Email:',
            //'required'=> true, //does not work here
            'attr' => array('class'=>'form-control'), //form-control-modif email-mask
        ));

        //used to display recaptcha error
        $builder->add( 'recaptcha', HiddenType::class, array(
            'mapped' => false,
            'error_bubbling' => false,
            'label' => false,
            'attr' => array('class'=>'form-control g-recaptcha1'),
        ));

        $builder->add('submit', SubmitType::class, array(
            'label' => 'Send me an email with a link to reset my password',
            'attr' => array('class' => 'btn btn-primary')
        ));
    }


    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\ResetPassword',
            'form_custom_value' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_resetpassword';
    }

}
