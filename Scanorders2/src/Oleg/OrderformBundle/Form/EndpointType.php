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

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EndpointType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        if( $params ) $this->params = $params;

        if( !array_key_exists('endpoint.location', $this->params) ) {
            $this->params['endpoint.location'] = true;
        }

        if( !array_key_exists('endpoint.system', $this->params) ) {
            $this->params['endpoint.system'] = true;
        }

        if( !array_key_exists('endpoint.location.label', $this->params) ) {
            $this->params['endpoint.location.label'] = "Location:";
        }

        if( !array_key_exists('endpoint.system.label', $this->params) ) {
            $this->params['endpoint.system.label'] = "System:";
        }

    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        ////////////// Location //////////////////////
        //use Endpoint object: destination - location

        $destinationLocationsOptions = array(
            'label' => $this->params['endpoint.location.label'],
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-location', 'type' => 'hidden'),
            'classtype' => 'location',
        );

        //locations default and preferred choices
        if( $this->params['cycle'] == 'new' && array_key_exists('destinationLocation', $this->params) ) {
            $destinationLocation = $this->params['destinationLocation'];
            $destinationLocationsOptions['data'] = $destinationLocation['data']->getId();
        }

        if( $this->params['endpoint.location'] == true ) {
            if( $this->params['cycle'] == 'show' ) {
                $builder->add('location', EntityType::class, array(
                    'label' => $this->params['endpoint.location.label'],
                    'required'=> false,
                    'multiple' => false,
                    'class' => 'OlegUserdirectoryBundle:Location',
                    'attr' => array('class' => 'combobox combobox-width')
                ));
            } else {
                $builder->add('location', CustomSelectorType::class, $destinationLocationsOptions);
            }
        }
        ////////////// EOF Location //////////////////////




        ////////////// System //////////////////////
        if( $this->params['endpoint.system'] == true ) {
            $builder->add('system', EntityType::class, array(
                'label' => $this->params['endpoint.system.label'],
                'required'=> false,
                'multiple' => false,
                'class' => 'OlegUserdirectoryBundle:SourceSystemList',
                'attr' => array('class' => 'combobox combobox-width')
            ));
        }
        ////////////// EOF System //////////////////////
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Endpoint',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_endpointtype';
    }
}
