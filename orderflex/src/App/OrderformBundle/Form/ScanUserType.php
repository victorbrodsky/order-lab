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

namespace App\OrderformBundle\Form;


use App\UserdirectoryBundle\Form\PermissionType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\UserdirectoryBundle\Form\PerSiteSettingsType;

use App\UserdirectoryBundle\Form\UserType;

class ScanUserType extends UserType
{

    public function addHookFields($builder) {

        //PerSiteSettingsType($this->user,$this->roleAdmin,$this->params)
        $builder->add('perSiteSettings', CollectionType::class, array(
            //'type' => new PerSiteSettingsType($this->user,$this->roleAdmin,$this->params),
            'entry_type' => PermissionType::class,
            'entry_options' => array(
                'form_custom_value_user' => $this->user,
                'form_custom_value_roleAdmin' => $this->roleAdmin,
                'form_custom_value' => $this->params
            ),
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__persitesettings__',
        ));

    }

}
