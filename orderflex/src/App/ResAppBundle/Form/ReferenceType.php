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

use App\UserdirectoryBundle\Entity\Identifier;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use App\UserdirectoryBundle\Form\DocumentType;
use App\ResAppBundle\Form\ResAppGeoLocationType;
use App\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ReferenceType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add('name', null, array(
            'label' => 'Last Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('firstName', null, array(
            'label' => 'First Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('title', null, array(
            'label' => 'Title:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('geoLocation', ResAppGeoLocationType::class, array(
            'form_custom_value' => $this->params,
            'data_class' => 'App\UserdirectoryBundle\Entity\GeoLocation',
            'label' => false,
            'required' => false
        ));

        if( $this->params['cycle'] != "download" && $this->params['cycle'] != "show" ) {
            $builder->add('institution', CustomSelectorType::class, array(
                'label' => 'Institution:',
                'attr' => array('class' => 'ajax-combobox-traininginstitution', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'traininginstitution'
            ));
        } else {
            $builder->add('institution', null, array(
                'label' => 'Institution:',
                'attr' => array('class' => 'combobox'),
                'required' => false
            ));
        }


        //Reference Letters
        $builder->add('documents', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Reference Letter(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('email', EmailType::class, array(
            'label' => 'E-Mail:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('phone', null, array(
            'label' => 'Phone Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('degree', null, array(
            'label' => 'Degree(s):',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('recLetterReceived', CheckboxType::class, array(
            'label' => 'Recommendation Letter Received:',
            'attr' => array('class'=>'checkbox')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\ResAppBundle\Entity\Reference',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_resappbundle_reference';
    }
}
