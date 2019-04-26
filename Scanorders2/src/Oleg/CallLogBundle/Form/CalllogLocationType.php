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

namespace Oleg\CallLogBundle\Form;



use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Oleg\UserdirectoryBundle\Form\GeoLocationType;
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

class CalllogLocationType extends AbstractType
{

    protected $params;
    protected $hasRoleSimpleView;


    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //echo "cycle=".$options['form_custom_value']['cycle']."<br>";
        $this->formConstructor($options['form_custom_value']);

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
        $locationTypesAttr['readonly'] = true;

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

        if(0) {
            ///////////////////////// tree node /////////////////////////
            //echo "LocationType institution=".$this->params['institution']."<br>";
            if ($this->params['institution']) {
                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                    $title = $event->getData();
                    $form = $event->getForm();

                    $label = null;
                    if ($title) {
                        $institution = $title->getInstitution();
                        if ($institution) {
                            $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                        }
                    }
                    if (!$label) {
                        $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
                    }

                    $form->add('institution', CustomSelectorType::class, array(
                        'label' => $label,
                        'required' => false,
                        'attr' => array(
                            'class' => 'ajax-combobox-compositetree combobox-without-add',
                            'type' => 'hidden',
                            'data-compositetree-bundlename' => 'UserdirectoryBundle',
                            'data-compositetree-classname' => 'Institution',
                            'data-readonly-parent-level' => '1'
                        ),
                        'classtype' => 'institution'
                    ));
                });
            }
            ///////////////////////// EOF tree node /////////////////////////
        }

        //Institution or Collaboration
        if( $this->params['defaultInstitution'] ) {
            $builder->add('institution', EntityType::class, array(
                'class' => 'OlegUserdirectoryBundle:Institution',
                'label' => "Institution or Collaboration:",
                'required' => false,
                'data' => $this->params['defaultInstitution'],
                'attr' => array('class' => 'combobox'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        //->leftJoin("u.locationTypes", "locationTypes")
                        ->where("u.level=0")
                        ->orderBy("u.orderinlist","ASC");
                },
            ));
        } else {
            $builder->add('institution', EntityType::class, array(
                'class' => 'OlegUserdirectoryBundle:Institution',
                'label' => "Institution or Collaboration:",
                'required' => false,
                'attr' => array('class' => 'combobox'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        //->leftJoin("u.locationTypes", "locationTypes")
                        ->where("u.level=0")
                        ->orderBy("u.orderinlist", "ASC");
                },
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
