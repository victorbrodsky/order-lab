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
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

//NOT USED
class UserPositionType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        echo 'cycle='.$this->params['cycle']."<br>";
//        $readonly = '';
//        if( strpos($this->params['cycle'],'show') !== false ) {
//            $readonly = 'readonly';
//        }
//        echo 'readonly='.$readonly."<br>";

        //hidden: set by js
        $builder->add( 'institution', null, array(
            'label' => false,
            'required'=> false,
            'data' => $this->params['treenode'],
            'attr' => array('class'=>'userposition-institution'),
        ));
//        $this->params['nodeid'] = '1';
//        if( $this->params['treenode'] ) {
//            $this->params['nodeid'] = $this->params['treenode']->getId();
//        }
//        $builder->add( 'institution', 'entity', array(
//            'class' => 'AppUserdirectoryBundle:Institution',
//            'label' => 'Institution:',
//            'required'=> true,
//            'multiple' => false,
//            //'attr' => array('class'=>'combobox combobox-width userposition-institution'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.id = :nodeid")
//                        ->setParameters( array(
//                            'nodeid' => $this->params['nodeid']
//                        ));
//                },
//        ));

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) {
//                $form = $event->getForm();
//                $userPosition = $event->getData();
//                $institution = null;
//
//                if( $userPosition ) {
//                    $institution = $userPosition->getInstitution();
//                }
//
//                if( $institution ) {
//                    $data = $institution;
//                } else {
//                    $data = null;
//                }
//
//                $form->add( 'institution', null, array(
//                    'label' => false,
//                    'required' => false,
//                    'data' => $data
//                ));
//            }
//        );

        //hidden: set by js
//        $builder->add( 'user', null, array(
//            'label' => false,
//            'required' => false,
//            'data' => $this->params['user']
//        ));
        $builder->add( 'user', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label' => false,
            'required'=> true,
            'multiple' => false,
            //'attr' => array('class'=>'combobox combobox-width userposition-user'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.id = :userid")
                        ->setParameters( array(
                            'userid' => $this->params['user']->getId()
                        ));
                },
        ));

        $attr = array('class'=>'combobox combobox-width userposition-positiontypes');
        if( strpos($this->params['cycle'],'show') !== false ) {
            $attr['readonly'] = 'readonly';
        }

        //visible as positionType combobox attached to an institution node
        $builder->add( 'positionTypes', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:PositionTypeList',
            'choice_label' => 'name',
            'label' => false,
            'required'=> false,
            'multiple' => true,
            'attr' => $attr,
            'data' => $this->params['positiontypes'],
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $userpos = $event->getData();
            $form = $event->getForm();
            if( !$userpos ) {
                return;
            }
            //echo 'userpos count='.coun($userpos)."<br>";
            //echo 'postypes count='.coun($userpos->getPositionTypes())."<br>";
            foreach( $userpos->getPositionTypes() as $type ) {
                echo '!PRE_SET_DATA user pos type='.$type."<br>";
                //echo 'PRE_SET_DATA user pos type='.$type."(inst=".$type->getInstitution().",userid=".$type->getUser()->getId()."<br>";
            }

//            $form->add('positionTypes',null,array(
//                'label' => false,
//                'multiple' => true,
//                'attr' => array('class' => 'combobox combobox-width userposition-positiontypes'),
//            ));

            //visible as positionType combobox attached to an institution node
//            $attr = array('class'=>'combobox combobox-width userposition-positiontypes');
//            if( strpos($this->params['cycle'],'show') !== false ) {
//                $attr['readonly'] = 'readonly';
//            }
//            $form->add( 'positionTypes', 'entity', array(
//                'class' => 'AppUserdirectoryBundle:PositionTypeList',
//                'choice_label' => 'name',
//                'label'=>'Position Type:',
//                'required'=> false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width userposition-positiontypes'),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('list')
//                            ->where("list.type = :typedef OR list.type = :typeadd")
//                            ->orderBy("list.orderinlist","ASC")
//                            ->setParameters( array(
//                                'typedef' => 'default',
//                                'typeadd' => 'user-added',
//                            ));
//                    },
//            ));

        });





//        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
//
//            $userPosition = $event->getData();
//            $form = $event->getForm();
//
//            echo "!!!userPosition:<br>";
//            print_r($userPosition);
//            echo "<br>";
//
//            if( !$userPosition ) {
//                return;
//            }
//
//
//
//        });



    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\UserPosition',
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_userposition';
    }
}
