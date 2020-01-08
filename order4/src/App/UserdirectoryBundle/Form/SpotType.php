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

use App\UserdirectoryBundle\Form\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SpotType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

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
            'disabled' => $read_only,
            'admin' => $roleAdmin,
            'currentUser' => $currentUser,
            'cycle' => $cycle,
            'em' => $em,
            'institution' => false,
            //'institution' => $this->params['institution'],
            'complexLocation' => $complexLocation,
            'readonlyLocationType' => $this->params['readonlyLocationType']
        );

        //LocationType($params)
        //echo "SpotType cycle=".$params['cycle']."<br>";
        //exit();
        $builder->add('currentLocation', LocationType::class, array(
            'form_custom_value' => $params,
            'data_class' => 'App\UserdirectoryBundle\Entity\Location',
            'label' => false,
        ));


//        $builder->add( 'mrnType', 'entity', array(
//            'class' => 'AppOrderformBundle:MrnType',
//            'choice_label' => 'name',
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\Spot',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_spottypetype';
    }
}
