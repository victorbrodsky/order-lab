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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class AuthorizedUserFilterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('roles', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:Roles',
            'label'=>false,
            'choice_label' => 'alias',
            'required'=> false,
            'multiple' => true,
            'choices' => $this->params['roles'],
            'attr' => array('class' => 'combobox', 'placeholder'=>'Search by Roles'),
        ));

        $builder->add('search', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array('class' => 'form-control submit-on-enter-field', 'placeholder'=>'Search by name, user name, or email'),
        ));

        $builder->add('condition', ChoiceType::class, array(
            'label' => 'Search Condition for Role filter',
            'required'=> true,
            'multiple' => false,
            'choices' => array(
                'AND' => 'AND',
                'OR' => 'OR',
            ),
            'attr' => array('class' => 'combobox', 'placeholder'=>'Search Condition for Role filter'),
        ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'form_custom_value' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'filter';
    }



}
