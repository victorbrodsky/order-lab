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
use Oleg\UserdirectoryBundle\Form\PerSiteSettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class AuthorizitaionUserType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('roles', ChoiceType::class, array(
            'choices' => $this->params['roles'],
            //'choices_as_values' => true,
            'label' => ucfirst($this->params['sitename']) . ' Role(s):',
            'attr' => array('class' => 'combobox combobox-width'),
            'multiple' => true,
        ));


        //PerSiteSettingsType(null, true, $this->params)
        $builder->add('perSiteSettings', PerSiteSettingsType::class, array(
            'form_custom_value_user' => null,
            'form_custom_value_roleAdmin' => true,
            'form_custom_value' => $this->params,
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\PerSiteSettings',
            'label' => false,
            'required' => false,
        ));

        $builder->add('emailNotification',CheckboxType::class, array(
            'label' => 'Inform authorized user by email:',
            'mapped' => false,
            'required' => false,
            'data' => true,
            'attr' => array('class' => 'form-control'),
        ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
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
