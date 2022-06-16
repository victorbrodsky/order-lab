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


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use App\UserdirectoryBundle\Entity\Training;

class TelephonySiteParameterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        

        $builder->add('twilioApiKey', null, array(
            'label' => 'Twilio Api Key:',
            'required' => false,
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('twilioSid', null, array(
            'label' => 'Twilio SID:',
            'required' => false,
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('fromPhoneNumber', null, array(
            'label' => 'Twilio From Phone Number (E. 164 format, i.e. +11234567890):',
            'required' => false,
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('phoneNumberVerification', CheckboxType::class, array(
            'label' => 'Phone number verification:',
            'required' => false,
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'edit' ) {
            $builder->add('update', SubmitType::class, array(
                'label' => "Update",
                'attr' => array('class' => 'btn btn-warning')
            ));
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\TelephonySiteParameter',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_telephonysiteparameter';
    }
}
