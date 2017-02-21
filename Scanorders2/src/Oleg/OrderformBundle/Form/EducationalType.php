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

use Oleg\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class EducationalType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //Display fields for Data Review
        if( $this->params['type'] == 'SingleObject' ) {


            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $holder = $event->getData();
                $form = $event->getForm();

                if( !$holder ) {
                    return;
                }

                ///////////////////// userWrappers /////////////////////
                $criterion = "user.roles LIKE '%ROLE_SCANORDER_COURSE_DIRECTOR%'";

                //add all users from UserWrappers for this educational
                foreach( $holder->getUserWrappers() as $userWrapper ) {
                    if( $userWrapper->getUser() && $userWrapper->getUser()->getId() ) {
                        $criterion = $criterion . " OR " . "user.id=" . $userWrapper->getUser()->getId();
                    }
                }

                $this->params['user.criterion'] = $criterion;   //array('role'=>'ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR');

                $this->params['name.label'] = 'Course Director (as entered by user for this order):';
                $this->params['user.label'] = 'Course Director:';

                $form->add('userWrappers', 'collection', array(
                    'type' => new UserWrapperType($this->params),
                    'label' => false,
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'prototype_name' => '__userwrapper__',
                ));
                ///////////////////// EOF userWrappers /////////////////////


                ///////////////////// primaryPrincipal /////////////////////
                $principalArr = array();
                $userWrappers = array();
                $comment = '';
                if( $holder ) {
                    $userWrappers = $holder->getUserWrappers();

                    //create array of choices: 'choices' => array("OPTION1"=>"TEXT1", "OPTION2"=>"TEXT2", "OPTION3"=>"TEXT3"),
                    foreach( $userWrappers as $userWrapper ) {
                        //echo $principal."<br>";
                        $principalArr[$userWrapper->getId()] = $userWrapper->getName();
                    }

                    if( $holder->getPrimarySet() ) {
                        $comment = ' for this order';
                    }
                }

                $form->add( 'primaryPrincipal', 'entity', array(
                    'class' => 'OlegUserdirectoryBundle:UserWrapper',
                    'label'=>'Primary Course Director (as entered by user'.$comment.'):',
                    'required'=> false,
                    'multiple' => false,
                    'attr' => array('class'=>'combobox combobox-width'),
                    'choices' => $userWrappers
                ));
                ///////////////////// EOF primaryPrincipal /////////////////////

            });


        } else {

            ///////////////////////// tree node /////////////////////////
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $title = $event->getData();
                $form = $event->getForm();

                $label = null;
                $mapper = array(
                    'prefix' => "Oleg",
                    'className' => "CourseTitleTree",
                    'bundleName' => "OrderformBundle",
                    'organizationalGroupType' => "CourseGroupType"
                );
                if( $title ) {
                    $educationalTitle = $title->getCourseTitle();
                    if( $educationalTitle ) {
                        $label = $this->params['em']->getRepository('OlegOrderformBundle:CourseTitleTree')->getLevelLabels($educationalTitle,$mapper) . ":";
                    }
                }
                if( !$label ) {
                    $label = $this->params['em']->getRepository('OlegOrderformBundle:CourseTitleTree')->getLevelLabels(null,$mapper) . ":";
                }
                //echo "label=".$label."<br>";

                $form->add('courseTitle', 'custom_selector', array(
                    'label' => $label,
                    'required' => false,
                    'attr' => array(
                        'class' => 'ajax-combobox-compositetree combobox-educational-courseTitle',
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'OrderformBundle',
                        'data-compositetree-classname' => 'CourseTitleTree',
                        'data-compositetree-initnode-function' => 'setOptionalUserEducational'
                    ),
                    'classtype' => 'courseTitle'
                ));
            });
            ///////////////////////// EOF tree node /////////////////////////

            //TODO: add mask: comma is not allowed
            $builder->add('userWrappers', 'custom_selector', array(
                'label' => 'Course Director(s):',
                'attr' => array('class' => 'combobox combobox-width combobox-optionaluser-educational', 'type' => 'hidden'),
                'required'=>false,
                'classtype' => 'optionalUserEducational'
            ));

        }

    }



    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Educational'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_educationaltype';
    }
}
