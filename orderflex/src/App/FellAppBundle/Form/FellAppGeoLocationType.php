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

namespace App\FellAppBundle\Form;



use App\UserdirectoryBundle\Entity\States; //process.py script: replaced namespace by ::class: added use line for classname=States


use App\UserdirectoryBundle\Entity\Countries; //process.py script: replaced namespace by ::class: added use line for classname=Countries


use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class FellAppGeoLocationType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $hasRoleSimpleView = false;
        if( array_key_exists('container', $this->params) ) {
            $loggedinUser = $this->params['container']->get('user_utility')->getLoggedinUser();
            if( $loggedinUser ) {
                //if( array_key_exists('security', $this->params) ) {
                //$hasRoleSimpleView = $this->params['container']->get('security.token_storage')->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
                //$hasRoleSimpleView = $this->params['security']->getUser()->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
                $hasRoleSimpleView = $loggedinUser->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
                //}
            }
        }

        $builder->add('street1',null,array(
            'label'=>'Street Address [Line 1]:',
            'attr' => array('class'=>'form-control geo-field-street1')
        ));

        $builder->add('street2',null,array(
            'label'=>'Street Address [Line 2]:',
            'attr' => array('class'=>'form-control geo-field-street2')
        ));

        if( $this->params['cycle'] != "download" && $this->params['cycle'] != "show" ) {

            $builder->add('city', CustomSelectorType::class, array(
                'label' => 'City:',
                'required' => false,
                'attr' => array('class' => 'combobox ajax-combobox-city', 'type' => 'hidden'),
                'classtype' => 'city'
            ));

            //state
            $stateArray = array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:States'] by [States::class]
                'class' => States::class,
                //'choice_label' => 'name',
                'label'=>'State:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width geo-field-state'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            );
            if( $this->params['cycle'] == 'new_standalone' ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:States'] by [States::class]
                $stateArray['data'] = $this->params['em']->getRepository(States::class)->findOneByName('New York');
            }
            $builder->add( 'state', EntityType::class, $stateArray);

            //country
            $countryArray = array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
                'class' => Countries::class,
                'choice_label' => 'name',
                'label'=>'Country:',
                'required'=> false,
                'multiple' => false,
                //'preferred_choices' => $preferredCountries,
                'attr' => array('class'=>'combobox combobox-width geo-field-country'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
            $countryArray['preferred_choices'] = $this->params['em']->getRepository(Countries::class)->findByName(array('United States'));
            if( $this->params['cycle'] == 'new_standalone' ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Countries'] by [Countries::class]
                $countryArray['data'] = $this->params['em']->getRepository(Countries::class)->findOneByName('United States');
            }
            $builder->add( 'country', EntityType::class, $countryArray);

        } else {
            $builder->add('city', null, array(
                'label' => 'City:',
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));

            $builder->add('state', null, array(
                'label' => 'State:',
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));

            $builder->add('country', null, array(
                'label' => 'Country:',
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ));
        }

        if( !$hasRoleSimpleView ) {
            $builder->add('county', null, array(
                'label' => 'County:',
                'attr' => array('class' => 'form-control geo-field-county')
            ));
        }

        $builder->add('zip',null,array(
            'label'=>'Zip Code:',
            'attr' => array('class'=>'form-control geo-field-zip')
        ));

    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\GeoLocation',
            'form_custom_value' => null
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_geolocation';
    }
}
