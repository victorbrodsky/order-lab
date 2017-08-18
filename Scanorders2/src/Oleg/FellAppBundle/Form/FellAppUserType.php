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

namespace Oleg\FellAppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class FellAppUserType extends UserType
{

    public function formConstructor( $params )
    {

        parent::formConstructor($params);

        if( $this->secAuthChecker->isGranted('ROLE_FELLAPP_ADMIN') || $this->secAuthChecker->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $this->roleAdmin = true;
        } else {
            $this->roleAdmin = false;
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        //Name and Preferred Contact Info
        $this->addUserInfos($builder);

//        $this->userTrainings($builder);
//
//        $this->userLocations($builder);
//
//        $this->addCredentials($builder);

    }



//    public function userLocations($builder) {
//        $params = array('disabled'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
//        $builder->add('locations', CollectionType::class, array(
//            'type' => new FellAppLocationType($params),
//            'label' => false,
//            'required' => false,
//            'allow_add' => true,
//            'allow_delete' => true,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__locations__',
//        ));
//
//        return $builder;
//    }


//    public function userTrainings($builder) {
//        $params = array('disabled'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
//        $builder->add('trainings', CollectionType::class, array(
//            'type' => new FellAppTrainingType($params),
//            'label' => false,
//            'required' => false,
//            'allow_add' => true,
//            'allow_delete' => true,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__trainings__',
//        ));
//
//        return $builder;
//    }

}
