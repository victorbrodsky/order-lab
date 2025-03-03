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



use App\UserdirectoryBundle\Entity\SourceSystemList; //process.py script: replaced namespace by ::class: added use line for classname=SourceSystemList


use App\UserdirectoryBundle\Entity\Location; //process.py script: replaced namespace by ::class: added use line for classname=Location
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ProcedureLocationType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $this->formConstructor($options['form_custom_value']);

        //Location Source System - array field location source
        $builder->add('source', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SourceSystemList'] by [SourceSystemList::class]
            'class' => SourceSystemList::class,
            'label' => 'Location Source System:',
            'required' => false,
            'data' => null,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->orderBy("list.orderinlist","ASC");
                },
        ));

        //Location Timestamp - property of location object
        //Location Provider - property of location object
        //Location Role - property of location object (use as a filter)

        $builder->add('field', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
            'class' => Location::class,
            'label' => 'Procedure Location',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.locationTypes", "locationTypes")
                        ->where("locationTypes.name = 'Inpatient Room'")
                        ->orderBy("list.orderinlist","ASC");
                },
        ));


        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => 'App\OrderformBundle\Entity\ProcedureLocation',
            'form_custom_value' => $this->params,
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\ProcedureLocation',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_orderformbundle_procedurelocationtype';
    }
}
