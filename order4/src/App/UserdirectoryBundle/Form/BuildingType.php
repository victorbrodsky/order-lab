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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\UserdirectoryBundle\Entity\Location;

class BuildingType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $standAloneLocation = false;
        if( strpos($this->params['cycle'],'_standalone') !== false && strpos($this->params['cycle'],'new') === false ) {
            $standAloneLocation = true;
        }

        //add user and list properties for stand alone location managemenet by LocationController
        if( $standAloneLocation ) {
            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cycle'] = $this->params['cycle'];
            $params['standalone'] = true;
            $mapper['className'] = "BuildingList";
            $mapper['bundleName'] = "AppUserdirectoryBundle";

            //ListType($params, $mapper)
            $builder->add('list', ListType::class, array(
                'form_custom_value' => $params,
                'form_custom_value_mapper' => $mapper,
                'data_class' => 'App\UserdirectoryBundle\Entity\BuildingList',
                'label' => false
            ));
        }

        $builder->add('name',null,array(
            'label'=>'Building Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('abbreviation',null,array(
            'label'=>'Building Abbreviation:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add( 'institutions', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:Institution',
            'choice_label' => 'name',
            'label'=>'Institution(s):',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));

        //GeoLocationType($this->params)
        $builder->add('geoLocation', GeoLocationType::class, array(
            'form_custom_value' => $this->params,
            'data_class' => 'App\UserdirectoryBundle\Entity\GeoLocation',
            'label' => false,
            'required' => false
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\BuildingList',
            'form_custom_value' => null
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_building';
    }
}
