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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ProcedureType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        if( !array_key_exists('show-tree-depth',$this->params) || !$this->params['show-tree-depth'] ) {
            $this->params['show-tree-depth'] = true; //show all levels
        }
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $readonly = false;
//        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure-patient' ) {
//            $readonly = true;
//        }
        $builder->add('name', 'collection', array(
            'type' => new ProcedureNameType($this->params, $this->entity),
            //'read_only' => $readonly,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Procedure Type:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__procedurename__',
        ));

        //children: if X=4, show only the first 4 levels (patient + encounter + procedure + accession)
        if( $this->params['show-tree-depth'] === true || intval($this->params['show-tree-depth']) >= 4 ) {
            $builder->add('accession', 'collection', array(
                'type' => new AccessionType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,//" ",
                'by_reference' => false,
                'prototype' => true,
                'prototype' => true,
                'prototype_name' => '__accession__',
            ));
        }


        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
//        if( array_key_exists('datastructure',$this->params) &&
//            ($this->params['datastructure'] == 'datastructure' || $this->params['datastructure'] == 'datastructure-patient' )
//        ) {

            $readonly = false;
            if( $this->params['datastructure'] == 'datastructure-patient' ) {
                $readonly = true;
            }

            //echo "flag datastructure=".$this->params['datastructure']."<br>";

            $builder->add('number', 'collection', array(
                'type' => new ProcedureNumberType($this->params, $this->entity),
                'read_only' => $readonly,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedurenumber__',
            ));

            $builder->add('date', 'collection', array(
                'type' => new ProcedureDateType($this->params, null),
                //'read_only' => $readonly,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__proceduredate__',
            ));

            $builder->add('location', 'collection', array(
                'type' => new ProcedureLocationType($this->params, null),
                //'read_only' => $readonly,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__procedurelocation__',
            ));

            $builder->add('provider', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                //'read_only' => $readonly,
                'label' => 'Provider:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where('u.roles LIKE :roles OR u=:user')
                            ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
                    },
            ));

//            $sources = array('WCMC Epic Ambulatory EMR','Written or oral referral');
//            $params = array('name'=>'Procedure','dataClass'=>'Oleg\OrderformBundle\Entity\ProcedureOrder','typename'=>'procedureorder','sources'=>$sources);
//            $builder->add('order', 'collection', array(
//                'type' => new GeneralOrderType($params, null),
//                'allow_add' => true,
//                'allow_delete' => true,
//                'required' => false,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__procedureorder__',
//            ));

        }
        if( 0 && array_key_exists('datastructure',$this->params) &&
            ($this->params['datastructure'] == 'datastructure' || $this->params['datastructure'] == 'datastructure-patient' )
        ) {
            $builder->add('date', 'collection', array(
                'type' => new ProcedureDateType($this->params, null),
                //'read_only' => $readonly,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__proceduredate__',
            ));
        }

        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $builder->add('message', 'collection', array(
                'type' => new MessageObjectType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__proceduremessage__',
            ));
        }

        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Procedure'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_proceduretype';
    }
}
