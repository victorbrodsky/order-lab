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


use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class ResearchLabType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        if( strpos($this->params['cycle'],'_standalone') === false ) {
            $readonly = true;
            $standalone = false;
        } else {
            $readonly = false;
            $standalone = true;
        }

        $hasRoleSimpleView = false;
        if( array_key_exists('container', $this->params) ) {
            $hasRoleSimpleView = $this->params['container']->get('security.token_storage')->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
        }

        //echo "cycle=".$this->params['cycle']."<br>";

        $builder->add( 'id', HiddenType::class, array(
            'label' => false,
            'attr' => array('class' => 'researchlab-id-field')
        ));

        if (!$hasRoleSimpleView) {
            $builder->add('foundedDate', DateType::class, array(
                'disabled' => $readonly,
                'label' => "Founded on:",
                'widget' => 'single_text',
                'required' => false,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control researchlab-foundedDate-field')
            ));

            $builder->add('dissolvedDate', DateType::class, array(
                'disabled' => $readonly,
                'label' => "Dissolved on:",
                'widget' => 'single_text',
                'required' => false,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control user-expired-end-date researchlab-dissolvedDate-field')
            ));
        }

        $locationAttr = array('class' => 'combobox combobox-width ajax-combobox-location', 'type' => 'hidden');
        if( $readonly ) {
            $locationAttr['readonly'] = true;
        }
        $builder->add('location', CustomSelectorType::class, array(
            //'disabled' => $readonly,
            'label' => "Location:",
            'required' => false,
            'attr' => $locationAttr,    //array('class' => 'combobox combobox-width ajax-combobox-location', 'type' => 'hidden'),
            'classtype' => 'location'
        ));

        $builder->add('weblink', null, array(
            'disabled' => $readonly,
            'label' => 'Web page link:',
            'attr' => array('class'=>'form-control researchlab-weblink-field')
        ));

        //Consider stanAlone for all cycles with _standalone, except new_standalone. Cycle new_standalone is exception because we don't show list attributes in creation page
        if( $standalone && strpos($this->params['cycle'],'new') === false ) {
            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cycle'] = $this->params['cycle'];
            $params['standalone'] = true;
            $mapper['className'] = "ResearchLab";
            $mapper['bundleName'] = "OlegUserdirectoryBundle";

            //new ListType($params, $mapper)
            $builder->add('list', ListType::class, array(
                'form_custom_value' => $params,
                'form_custom_value_mapper' => $mapper,
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\ResearchLab',
                'label' => false
            ));
        }

        //echo "subjectUser=".$this->params['subjectUser']."<br>";

        $builder->add( 'institution', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:Institution',
            'label'=> "Research Lab Title:",
            'required'=> false,
            'multiple' => false,
            //'choice_label' => 'getTreeName', //getNodeNameWithRoot
            'attr' => array('class'=>'combobox combobox-width ajax-combobox-researchlab'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.organizationalGroupType","organizationalGroupType")
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->andWhere("organizationalGroupType.name = :organizationalGroupTypeName")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'organizationalGroupTypeName' => 'Research Lab'
                    ));
            },
        ));

        if( !$standalone ) {

            ////////////////////////// comment and pi /////////////////////////
            //pi and comment
            //pi and common are arrays, but we should show only objects belonging to the subjectUser,
            //so we relay only on dummy variables and set them according to the current lab
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $lab = $event->getData();
                $form = $event->getForm();

//                $form->add('name', 'employees_custom_selector', array(
//                    'disabled' => ($lab && $lab->getId() ? true : false),
//                    'label' => "Research Lab Title:",
//                    'required' => false,
//                    'attr' => array('class' => 'combobox combobox-width ajax-combobox-researchlab', 'type' => 'hidden'),
//                    'classtype' => 'researchlab'
//                ));

                if ($lab) {

                    foreach ($lab->getComments() as $comment) {
                        if ($comment->getAuthor() && $comment->getAuthor()->getId() == $this->params['subjectUser']->getId()) {
                            //preset comment dummy for current lab
                            $lab->setCommentDummy($comment->getComment());
                        }
                    }

                    foreach ($lab->getPis() as $pi) {
                        if ($pi && $pi == true && $pi->getPi()->getId() == $this->params['subjectUser']->getId()) {
                            //preset pi dummy for current lab
                            $lab->setPiDummy(true);
                        }
                    }

                }


            });

            if (!$hasRoleSimpleView){
                $builder->add('commentDummy', TextareaType::class, array(
                    //'mapped' => false,
                    'required' => false,
                    'label' => 'Comment:',
                    'attr' => array('class' => 'textarea form-control researchlab-commentDummy-field')
                ));
            }

            $builder->add('piDummy', CheckboxType::class, array(
                //'mapped' => false,
                'required' => false,
                'label' => 'Principal Investigator of this Lab:',
                'attr' => array('class'=>'form-control researchlab-piDummy-field', 'style'=>'margin:0')
            ));

            ////////////////////////// EOF comment and pi /////////////////////////

        } else {

            //use name as lab unique identifier
            $builder->add('name',null,array(
                'label'=>"Research Lab Other Title (Not Institution):",
                'required' => true,
                'attr' => array('class' => 'form-control')
            ));

        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\ResearchLab',
            'form_custom_value' => null
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_researchlab';
    }
}
