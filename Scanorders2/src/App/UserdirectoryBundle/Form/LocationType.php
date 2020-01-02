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



use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Location;

class LocationType extends AbstractType
{

    protected $params;
    protected $hasRoleSimpleView;


    public function formConstructor( $params )
    {
        $this->params = $params;

        if( !array_key_exists('institution', $this->params) ) {
            $this->params['institution'] = true;
        }

        if( !array_key_exists('complexLocation', $this->params) ) {
            $this->params['complexLocation'] = true;
        }

        if( !array_key_exists('readonlyLocationType', $this->params) ) {
            $this->params['readonlyLocationType'] = false;
        }

        $this->hasRoleSimpleView = false;
        if( array_key_exists('container', $this->params) ) {
            $this->hasRoleSimpleView = $this->params['container']->get('security.token_storage')->getToken()->getUser()->hasRole("ROLE_USERDIRECTORY_SIMPLEVIEW");
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //echo "cycle=".$options['form_custom_value']['cycle']."<br>";
        $this->formConstructor($options['form_custom_value']);

        if (strpos($this->params['cycle'], '_standalone') === false) {
            $standalone = false;
        } else {
            $standalone = true;
        }

        $builder->add('id', HiddenType::class, array(
            'label' => false,
            'attr' => array('class' => 'user-object-id-field')
        ));

        $builder->add('name', null, array(
            'label' => "Location's Name:",
            'attr' => array('class' => 'form-control user-location-name-field')
        ));

        $builder->add('phone', null, array(
            'label' => 'Phone Number:',
            'attr' => array('class' => 'form-control user-location-phone-field')
        ));

        $builder->add('building', CustomSelectorType::class, array(
            'label' => 'Building:',
            'attr' => array('class' => 'combobox ajax-combobox-building', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'building'
        ));

        $builder->add('room', CustomSelectorType::class, array(
            'label' => 'Room Number:',
            'attr' => array('class' => 'combobox ajax-combobox-room', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'room'
        ));

        $builder->add('suite', CustomSelectorType::class, array(
            'label' => 'Suite:',
            'attr' => array('class' => 'combobox ajax-combobox-suite', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'suite'
        ));

        $builder->add('floor', CustomSelectorType::class, array(
            'label' => 'Floor:',
            'attr' => array('class' => 'combobox ajax-combobox-floor', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'floor'
        ));

        if( !$this->hasRoleSimpleView ) {
            $builder->add('floorSide', null, array(
                'label' => "Floor Side:",
                'attr' => array('class' => 'form-control user-location-floorside-field')
            ));
        }

        $builder->add('comment', TextareaType::class, array(
            //'max_length'=>5000,
            'required'=>false,
            'label'=>'Comment:',
            'attr' => array('class'=>'textarea form-control'),
        ));

        //locationTypes
        $locationTypesAttr = array('class'=>'combobox combobox-width user-location-locationTypes');
        if( $this->params['readonlyLocationType'] ) {
            $locationTypesAttr['readonly'] = true;
        }
        $builder->add('locationTypes', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:LocationTypeList',
            'label' => "Location Type:",
            //'disabled' => $this->params['readonlyLocationType'],
            'multiple' => true,
            'attr' => $locationTypesAttr,   //array('class'=>'combobox combobox-width user-location-locationTypes'),
            'required' => false,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where('list.type != :disabletype AND list.type != :drafttype')
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array('disabletype'=>'disabled','drafttype'=>'draft')
                );
            }
        ));

        //GeoLocationType($this->params)
        $builder->add('geoLocation', GeoLocationType::class, array(
            'form_custom_value' => $this->params,
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\GeoLocation',
            'label' => false,
            'required' => false
        ));

        //complexLocation
        if( $this->params['complexLocation'] ) {
            //echo "show complex location<br>";

            $builder->add('name', null, array(
                'label' => "* Location's Name:",
                'attr' => array('class' => 'form-control user-location-name-field', 'required' => 'required')
            ));

            if( $this->params['cycle'] != "new_standalone" ) {
                $baseUserAttr = new Location();
                $statusAttr = array('class' => 'combobox combobox-width');
                if( $this->params['disabled'] ) {
                    $statusAttr['readonly'] = true;
                }
                $builder->add('status', ChoiceType::class, array( //flipped
                    'disabled' => ($this->params['disabled'] ? true : false),
//                    'choices' => array(
//                        $baseUserAttr::STATUS_UNVERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED),
//                        $baseUserAttr::STATUS_VERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED)
//                    ),
                    'choices' => array(
                        $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED) => $baseUserAttr::STATUS_UNVERIFIED,
                        $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED) => $baseUserAttr::STATUS_VERIFIED
                    ),
                    'invalid_message' => 'invalid value: location status',
                    //'choices_as_values' => true,
                    'label' => "Status:",
                    'required' => true,
                    'attr' => $statusAttr,  //array('class' => 'combobox combobox-width'),
                ));
            }

            $builder->add('email',null,array(
                'label'=>'E-Mail:',
                'attr' => array('class'=>'form-control')
            ));

            $builder->add('pager', null, array(
                'label' => 'Pager Number:',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('mobile', null, array(
                'label' => 'Mobile Number:',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('ic', null, array(
                'label' => 'Intercom (IC):',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('fax', null, array(
                'label' => 'Fax:',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('mailbox', CustomSelectorType::class, array(
                'label' => 'Mailbox:',
                'attr' => array('class' => 'ajax-combobox-mailbox', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'mailbox'
            ));

            $builder->add('associatedCode',null,array(
                'label'=>'Associated Institutional Code for this Location:',
                'attr' => array('class'=>'form-control')
            ));

            //In Locations, show the CLIA, and PFI fields only to Administrators and the user himself.
            if( $this->params['admin'] || $this->params['currentUser'] ) {
                $builder->add('associatedClia',null,array(
                    'label'=>'Associated Clinical Laboratory Improvement Amendments (CLIA) Number:',
                    'attr' => array('class'=>'form-control')
                ));

                $builder->add('associatedCliaExpDate', DateType::class, array(
                    'label' => "Associated CLIA Expiration Date:",
                    'widget' => 'single_text',
                    'required' => false,
                    'format' => 'MM/dd/yyyy',
                    'attr' => array('class' => 'datepicker form-control allow-future-date'),
                ));

                $builder->add('associatedPfi',null,array(
                    'label'=>'Associated Governmental Permanent Facility Identifier (PFI) Number for this Location:',
                    'attr' => array('class'=>'form-control')
                ));
            }

            //assistant
            if( $this->params['cycle'] != "new_standalone" ) {
                $builder->add( 'assistant', EntityType::class, array(
                    'class' => 'OlegUserdirectoryBundle:User',
                    'label'=> "Assistant(s):",
                    'required'=> false,
                    'multiple' => true,
                    'attr' => array('class'=>'combobox combobox-width'),
                    'query_builder' => function(EntityRepository $er) {
                        if( array_key_exists('subjectUser', $this->params) ) {
                            return $er->createQueryBuilder('list')
                                ->leftJoin("list.employmentStatus", "employmentStatus")
                                ->leftJoin("employmentStatus.employmentType", "employmentType")
                                ->where("list.id != :userid AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                                ->leftJoin("list.infos", "infos")
                                ->orderBy("infos.displayName","ASC")
                                ->setParameters( array('userid' => $this->params['subjectUser']->getId()) );
                        } else {
                            return $er->createQueryBuilder('list')
                                ->leftJoin("list.infos", "infos")
                                ->orderBy("infos.displayName","ASC");
                        }
                    },
                ));
            }
        }//if complexLocation


        ///////////////////////// tree node /////////////////////////
        //echo "LocationType institution=".$this->params['institution']."<br>";
        if( $this->params['institution'] ) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $title = $event->getData();
                $form = $event->getForm();

                $label = null;
                if( $title ) {
                    $institution = $title->getInstitution();
                    if( $institution ) {
                        $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                    }
                }
				if( !$label ) {
					$label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
				}

                $form->add('institution', CustomSelectorType::class, array(
                    'label' => $label,
                    'required' => false,
                    'attr' => array(
                        'class' => 'ajax-combobox-compositetree',
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'UserdirectoryBundle',
                        'data-compositetree-classname' => 'Institution'
                    ),
                    'classtype' => 'institution'
                ));
            });
        }
        ///////////////////////// EOF tree node /////////////////////////


        //Privacy
        $arrayOptions = array(
            'class' => 'OlegUserdirectoryBundle:LocationPrivacyList',
            'label' => "Location Privacy (who can see this contact info):",
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'required' => true,
        );

        //get default privacy
        if( $this->params['cycle'] == "new_standalone" ) {
            $defaultPrivacy = $this->params['em']->getRepository('OlegUserdirectoryBundle:LocationPrivacyList')->findOneByName("Anyone can see this contact information");
            $arrayOptions['data'] = $defaultPrivacy;
        }

        $builder->add('privacy', EntityType::class, $arrayOptions);


        //add user (Inhabitant) for all stand alone location management by LocationController
        if( $standalone ) {
            //user
            $builder->add('user', CustomSelectorType::class, array(
                'label'=> "Inhabitant / Contact:",
                'attr' => array('class' => 'combobox combobox-width combobox-without-add ajax-combobox-locationusers', 'type' => 'hidden'),
                'required' => false,
                //'multiple' => false,
                'classtype' => 'locationusers'
            ));
//            $builder->add( 'user', 'entity', array(
//                'class' => 'OlegUserdirectoryBundle:User',
//                'label'=> "Inhabitant / Contact:",
//                'required'=> false,
//                'multiple' => false,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                        $list = $er->createQueryBuilder('list')
//                            ->select()
//                            ->leftJoin("list.infos", "infos")
//                            ->orderBy("infos.displayName","ASC");
//                        return $list;
//                    },
//            ));

//            $builder->add('removable','checkbox',array(
//                'label' => "Removable:",
//            ));
        }

        //Consider stanAlone for all cycles with _standalone, except new_standalone. Cycle new_standalone is exception because we don't show list attributes in creation page
        if( $standalone && strpos($this->params['cycle'],'new') === false ) {
            //list attributes
            $params = array();
            $mapper = array();
            $params['user'] = $this->params['user'];
            $params['cycle'] = $this->params['cycle'];
            $params['standalone'] = true;
            $mapper['className'] = "Location";
            $mapper['bundleName'] = "OlegUserdirectoryBundle";

            //ListType($params, $mapper)
            $builder->add('list', ListType::class, array(
                'form_custom_value' => $params,
                'form_custom_value_mapper' => $mapper,
                'data_class' => 'Oleg\UserdirectoryBundle\Entity\Location',
                'label' => false
            ));
        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Location',
            'form_custom_value' => null,
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_location';
    }

}
