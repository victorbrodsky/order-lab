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

namespace Oleg\UserdirectoryBundle\Form;

use Oleg\UserdirectoryBundle\Form\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class SpotType extends AbstractType
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

//        $builder->add('id',null,array(
//            'label' => "ID:",
//            'attr' => array('class'=>'form-control')
//        ));

        if( !array_key_exists('complexLocation', $this->params) ) {
            $this->params['complexLocation'] = true;
        }

        if( !array_key_exists('readonlyLocationType', $this->params) ) {
            $this->params['readonlyLocationType'] = false;
        }

        $currentUser = true;
        $cycle = $this->params['cycle'];
        $em = $this->params['em'];
        $roleAdmin = true;
        $read_only = false;
        $complexLocation = $this->params['complexLocation'];

        $params = array(
            'read_only' => $read_only,
            'admin' => $roleAdmin,
            'currentUser' => $currentUser,
            'cycle' => $cycle,
            'em' => $em,
            'institution' => false,
            'complexLocation' => $complexLocation,
            'readonlyLocationType' => $this->params['readonlyLocationType']
        );

        $builder->add('currentLocation', new LocationType($params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Location',
            'label' => false,
        ));


//        $builder->add( 'mrnType', 'entity', array(
//            'class' => 'OlegOrderformBundle:MrnType',
//            'property' => 'name',
//            'label' => "Patient's MRN Type:",
//            'required'=> false,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.type = :typedef OR list.type = :typeadd")
//                        ->orderBy("list.orderinlist","ASC")
//                        ->setParameters( array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                        ));
//                },
//        ));
//
//        $builder->add('mrn',null,array(
//            'label' => "Patient's MRN:",
//            'attr' => array('class'=>'form-control')
//        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Spot',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_spottypetype';
    }
}
