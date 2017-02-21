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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ProcedureLocationType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //Location Source System - array field location source
        $builder->add('source', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
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

        $builder->add('field', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Location',
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


        $builder->add('others', new ArrayFieldType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureLocation',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureLocation',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_procedurelocationtype';
    }
}
