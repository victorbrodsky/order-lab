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

namespace App\CrnBundle\Form;

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\LoggerFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrnLoggerFilterType extends LoggerFilterType
{

    public function addOptionalFields( $builder ) {

        if( array_key_exists('showCapacity', $this->params) ) {
            $showCapacity = $this->params['showCapacity'];
        } else {
            $showCapacity = false;
        }

        //Capacity
        if( $this->params['sitename'] == "crn" && $showCapacity ) {
            $capacities = array(
                "Submitter" => "Submitter",
                "Attending" => "Attending"
            );
            $builder->add('capacity', ChoiceType::class, array(
                'label' => false,
                'required'=> false,
                'choices' => $capacities,
                //'choices_as_values' => true,
                'attr' => array('class' => 'combobox', 'placeholder' => 'Capacity'),
            ));
        }

    }

}
