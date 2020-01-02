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

namespace Oleg\VacReqBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\UserType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class VacReqUserComboboxType extends UserType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('users', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => $this->params['btnName'].":",    //"New Submitters:",
            'required' => false,
            'multiple' => true,
            //'choice_label' => 'name',
            'attr' => array('class' => 'combobox vacreq-emailusers'),
            //'disabled' => ($this->params['review'] ? true : false),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('user')
                    ->leftJoin("user.infos","infos")
                    ->leftJoin("user.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                    ->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->orderBy("infos.lastName","ASC");
            },
        ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'csrf_protection' => false,
            'form_custom_value' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'oleg_vacreqbundle_user_participants';
    }

}
