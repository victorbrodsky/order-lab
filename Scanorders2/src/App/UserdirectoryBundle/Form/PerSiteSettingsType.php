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

//use App\UserdirectoryBundle\Form\InstitutionType;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class PerSiteSettingsType extends AbstractType
{

    protected $user;
    protected $roleAdmin;
    protected $params;


    public function formConstructor( $user, $roleAdmin, $params )
    {
        $this->user = $user;
        $this->roleAdmin = $roleAdmin;
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value_user'],$options['form_custom_value_roleAdmin'],$options['form_custom_value']);

        if( $this->roleAdmin ) {

            $builder->add( 'permittedInstitutionalPHIScope', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:Institution',
                //'choice_label' => 'name',
                'choice_label' => 'getTreeName',
                'label'=>'Order data visible to members of (Institutional PHI Scope):',
                'required' => false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.types","institutiontype")
                        //->where("(list.type = :typedef OR list.type = :typeadd) AND institutiontype.name = :medicalInstitution")
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                            //'medicalInstitution' => 'Medical'
                        ));
                },
            ));

        } //roleAdmin


        if( !array_key_exists('simple-form', $this->params) || !$this->params['simple-form'] ) {

            $builder->add('tooltip', CheckboxType::class, array(
                'required' => false,
                'label' => 'Show tool tips for locked fields:',
                'attr' => array('class'=>'form-control', 'style'=>'margin:0')
            ));


            //ScanOrdersServicesScope
            $builder->add( 'scanOrdersServicesScope', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:Institution',
                //'choice_label' => 'name',
                'choice_label' => 'getTreeName',
                'label'=>'Service(s) Scope:',
                'required'=> false,
                'multiple' => true,
                //'empty_value' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('list')
                            ->leftJoin("list.parent","department")
                            ->where("(list.type = :typedef OR list.type = :typeadd) AND department.name = :pname")
                            ->orderBy("list.orderinlist","ASC")
                            ->setParameters( array(
                                'typedef' => 'default',
                                'typeadd' => 'user-added',
                                'pname' => 'Pathology and Laboratory Medicine'
                                //'medicalInstitution' => 'Medical'
                            ));
                    },
            ));

            //chiefServices
            $builder->add( 'chiefServices', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:Institution',
                //'choice_label' => 'name',
                'choice_label' => 'getTreeName',
                'label'=>'Chief of the following Service(s) for Scope:',
                'required'=> false,
                'multiple' => true,
                //'empty_value' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('list')
                            ->leftJoin("list.parent","department")
                            ->where("(list.type = :typedef OR list.type = :typeadd) AND department.name = :pname")
                            ->orderBy("list.orderinlist","ASC")
                            ->setParameters( array(
                                'typedef' => 'default',
                                'typeadd' => 'user-added',
                                'pname' => 'Pathology and Laboratory Medicine'
                                //'medicalInstitution' => 'Medical'
                            ));
                    },
            ));

            if( array_key_exists('em', $this->params) ) {
                //defaultInstitution
                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                    $title = $event->getData();
                    $form = $event->getForm();

                    $label = null;
                    if( $title ) {
                        $institution = $title->getDefaultInstitution();
                        if( $institution ) {
                            $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels($institution);
                        }
                    }
                    if( !$label ) {
                        $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels(null);
                    }

                    //echo "show defaultInstitution label=".$label."<br>";

                    $form->add('defaultInstitution', CustomSelectorType::class, array(
                        'label' => 'Organizational Group ' . $label . ':',
                        'disabled' => !$this->roleAdmin,
                        'required' => false,
                        'attr' => array(
                            'class' => 'ajax-combobox-compositetree',
                            'type' => 'hidden',
                            'data-compositetree-bundlename' => 'UserdirectoryBundle',
                            'data-compositetree-classname' => 'Institution',
                            'data-label-prefix' => 'Organizational Group'
                        ),
                        'classtype' => 'institution'
                    ));

                    $prefix = "Organizational Group for new user's default values in Employee Directory - ";
                    $form->add('organizationalGroupDefault', CustomSelectorType::class, array(
                        'label' => $prefix." ".$label.':',
                        //'error_bubbling' => true,
                        'required' => false,
                        'attr' => array(
                            'class' => 'ajax-combobox-compositetree',
                            'type' => 'hidden',
                            'data-compositetree-bundlename' => 'UserdirectoryBundle',
                            'data-compositetree-classname' => 'Institution',
                            'data-label-prefix' => $prefix
                        ),
                        'classtype' => 'institution'
                    ));

                });
            }

        } //if simple form

//        $builder->add('institution', CustomSelectorType::class, array(
//            'label' => 'Default Institution:',
//            'attr' => array('class' => 'ajax-combobox-compositetree combobox-without-add', 'type' => 'hidden'),
//            'required' => false,
//            'classtype' => 'institution'
//        ));

        //department. User should be able to add institution to administrative or appointment titles
//        $builder->add('defaultDepartment', CustomSelectorType::class, array(
//            'label' => "Default Department:",
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-department combobox-without-add', 'type' => 'hidden'),
//            'classtype' => 'department'
//        ));
//
//        //division. User should be able to add institution to administrative or appointment titles
//        $builder->add('defaultDivision', CustomSelectorType::class, array(
//            'label' => "Default Division:",
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-division combobox-without-add', 'type' => 'hidden'),
//            'classtype' => 'division'
//        ));
//
//        $builder->add( 'defaultService', CustomSelectorType::class, array(
//            'label'=>'Default Service:',
//            'required'=>false,
//            'attr' => array('class' => 'ajax-combobox-service combobox-without-add', 'type' => 'hidden'),
//            'classtype' => 'service'
//        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\PerSiteSettings',
            'csrf_protection' => false,
            'form_custom_value_user' => null,
            'form_custom_value_roleAdmin' => null,
            'form_custom_value' => null,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_persitesettings';
    }
}
