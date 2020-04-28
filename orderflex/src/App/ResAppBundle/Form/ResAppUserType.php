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

namespace App\ResAppBundle\Form;

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class ResAppUserType extends UserType
{

    public function formConstructor( $params )
    {

        parent::formConstructor($params);

        if( $this->secAuthChecker->isGranted('ROLE_RESAPP_ADMIN') || $this->secAuthChecker->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $this->roleAdmin = true;
        } else {
            $this->roleAdmin = false;
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        //Name and Preferred Contact Info
        $this->addUserInfos($builder); //testing disabled

        //DOB
        $builder->add('credentials', ResAppCredentialsType::class, array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Credentials',
            'label' => false,
            'required' => false,
        ));


    }


}
