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


use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use App\UserdirectoryBundle\Util\TimeZoneUtil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class OrganizationalGroupDefaultType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('id',HiddenType::class,array('label'=>false));

        $builder->add('primaryPublicUserIdType', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:UsernameType',
            'choice_label' => 'name',
            'label' => "Primary Public User ID Type:",
            'required' => false,
            'multiple' => false,
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

        $builder->add('email', null, array(
            'label' => 'Preferred Email:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('roles', ChoiceType::class, array( //flipped
            'choices' => $this->params['roles'],
            //'choices_as_values' => true,
            'label' => 'Role(s):',
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
        ));

        //timezone
        $tzUtil = new TimeZoneUtil();
        $builder->add( 'timezone', ChoiceType::class, array( //flipped
            'label' => 'Time Zone:',
            //'label' => $translator->translate('timezone',$formtype,'Time Zone:'),
            'choices' => $tzUtil->tz_list(),
            //'choices_as_values' => true,
            'invalid_message' => 'invalid value: timezone',
            'required' => true,
            'preferred_choices' => array('America/New_York'),
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add( 'languages', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:LanguageList',
            'label'=> "Language(s):",
            'required'=> false,
            'multiple' => true,
            'choice_label' => 'fulltitle',
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

        $builder->add( 'locale', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:LocaleList',
            'label'=> "Locale:",
            'required'=> false,
            'multiple' => false,
            'choice_label' => 'fulltitle',
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

        $builder->add( 'showToInstitutions', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:Institution',
            //'choice_label' => 'name',
            'choice_label' => 'getTreeName',
            'label'=>'Only show this profile to members of the following institution(s):',
            'required'=> false,
            'multiple' => true,
            //'empty_value' => false,
            'attr' => array('class' => 'combobox combobox-width user-preferences-showToInstitutions'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("(list.type = :typedef OR list.type = :typeadd) AND list.level = :level")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'level' => 0
                    ));
            },
        ));

        $builder->add('tooltip', CheckboxType::class, array(
            'required' => false,
            'label' => 'Show tool tips for locked fields:',
            'attr' => array('class'=>'form-control', 'style'=>'margin:0')
        ));

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

        $builder->add('employmentType',null,array(
            'label'=>"Employee Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add('locationTypes',EntityType::class,array(
            'class' => 'AppUserdirectoryBundle:LocationTypeList',
            'label' => "Location Type:",
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'required' => false,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where('list.type != :disabletype AND list.type != :drafttype')
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array('disabletype'=>'disabled','drafttype'=>'draft')
                    );
            }
        ));

        $builder->add('city', CustomSelectorType::class, array(
            'label' => 'Location City:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-city', 'type' => 'hidden'),
            'classtype' => 'city'
        ));

        //state
        $builder->add( 'state', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:States',
            //'choice_label' => 'name',
            'label'=>'Location State:',
            'required'=> false,
            'multiple' => false,
            'data' => $this->params['em']->getRepository('AppUserdirectoryBundle:States')->findOneByName('New York'),
            'attr' => array('class'=>'combobox combobox-width geo-field-state'),
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

        $builder->add('zip',null,array(
            'label'=>'Zip Code:',
            'attr' => array('class'=>'form-control geo-field-zip')
        ));

        //country
        $builder->add( 'country', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:Countries',
            'choice_label' => 'name',
            'label'=>'Location Country:',
            'required'=> false,
            'multiple' => false,
            'data' => $this->params['em']->getRepository('AppUserdirectoryBundle:Countries')->findOneByName('United States'),
            'preferred_choices' => $this->params['em']->getRepository('AppUserdirectoryBundle:Countries')->findByName(array('United States')),
            'attr' => array('class'=>'combobox combobox-width geo-field-country'),
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

        $builder->add( 'state', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:States',
            //'choice_label' => 'name',
            'label'=>'Location State:',
            'required'=> false,
            'multiple' => false,
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


        $builder->add( 'medicalLicenseCountry', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:Countries',
            'choice_label' => 'name',
            'label'=>'Medical License Country:',
            'required'=> false,
            'multiple' => false,
            'data' => $this->params['em']->getRepository('AppUserdirectoryBundle:Countries')->findOneByName('United States'),
            'preferred_choices' => $this->params['em']->getRepository('AppUserdirectoryBundle:Countries')->findByName(array('United States')),
            'attr' => array('class'=>'combobox combobox-width geo-field-country'),
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

        $builder->add( 'medicalLicenseState', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:States',
            //'choice_label' => 'name',
            'label'=>'Medical License State:',
            'required'=> false,
            'multiple' => false,
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


        ///////////////////////// tree node /////////////////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $title = $event->getData();
            $form = $event->getForm();

            $label = null;
            if( $title ) {
                $institution = $title->getInstitution();
                if( $institution ) {
                    $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                }
            }
            if( !$label ) {
                $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
            }

            $targetPrefix = "Organizational Group for new user's default values in Employee Directory - ";
            $form->add('institution', CustomSelectorType::class, array(
                'label' => $targetPrefix." ".$label,
                //'error_bubbling' => true,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => $targetPrefix
                ),
                'classtype' => 'institution'
            ));

            $form->add('defaultInstitution', CustomSelectorType::class, array(
                'label' => 'Organizational Group ' . $label,
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

            //administrativeTitleInstitution
            $form->add('administrativeTitleInstitution', CustomSelectorType::class, array(
                'label' => "Administrative Title ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Administrative Title'
                ),
                'classtype' => 'institution'
            ));

            //academicTitleInstitution
            $form->add('academicTitleInstitution', CustomSelectorType::class, array(
                'label' => "Academic Appointment Title ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Academic Appointment Title'
                ),
                'classtype' => 'institution'
            ));

            //medicalTitleInstitution
            $form->add('medicalTitleInstitution', CustomSelectorType::class, array(
                'label' => "Medical Appointment Title ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Medical Appointment Title'
                ),
                'classtype' => 'institution'
            ));

            $form->add('locationInstitution', CustomSelectorType::class, array(
                'label' => "Location ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Location'
                ),
                'classtype' => 'institution'
            ));

            $form->add('employmentInstitution', CustomSelectorType::class, array(
                'label' => "Organizational Group for Employment Period ".$label,
                'required' => false,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution',
                    'data-label-prefix' => 'Organizational Group for Employment Period'
                ),
                'classtype' => 'institution'
            ));
        });
        ///////////////////////// EOF tree node /////////////////////////

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\OrganizationalGroupDefault',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_organizationalgroupdefaults';
    }
}
