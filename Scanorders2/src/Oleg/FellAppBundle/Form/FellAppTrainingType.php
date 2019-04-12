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

namespace Oleg\FellAppBundle\Form;


use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Oleg\FellAppBundle\Form\FellAppGeoLocationType;
use Oleg\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Training;

class FellAppTrainingType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('startDate', DateType::class, array(
            'label' => 'Start Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('completionDate', DateType::class, array(
            'label' => 'Completion Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        if( $this->params['cycle'] != "download" ) {

            $builder->add('degree', null, array(
                'label' => 'Degree:',
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-trainingdegree')
            ));

            $builder->add('majors', CustomSelectorType::class, array(
                'label' => 'Major:',
                'attr' => array('class' => 'ajax-combobox-trainingmajors', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'trainingmajors'
            ));

            $builder->add('institution', CustomSelectorType::class, array(
                'label' => 'Educational Institution:',
                'attr' => array('class' => 'ajax-combobox-traininginstitution', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'traininginstitution'
            ));

            //residencySpecialty
            $builder->add('residencySpecialty', CustomSelectorType::class, array(
                'label' => 'Residency Specialty:',
                'attr' => array('class' => 'ajax-combobox-residencyspecialty', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'residencyspecialty'
            ));

            //jobTitle
            $builder->add('jobTitle', CustomSelectorType::class, array(
                'label' => 'Job or Experience Title:',
                'attr' => array('class' => 'ajax-combobox-jobtitle', 'type' => 'hidden'),
                'required' => false,
                'classtype' => 'jobTitle'
            ));
        } else {
            $builder->add('degree', null, array(
                'label' => 'Degree:',
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('majors', null, array(
                'label' => 'Major:',
                'attr' => array('class' => 'form-control'),
            ));

            $builder->add('institution', null, array(
                'label' => 'Educational Institution:',
                'attr' => array('class' => 'form-control'),
            ));

            //residencySpecialty
            $builder->add('residencySpecialty', null, array(
                'label' => 'Residency Specialty:',
                'attr' => array('class' => 'form-control'),
            ));

            //jobTitle
            $builder->add('jobTitle', null, array(
                'label' => 'Job or Experience Title:',
                'attr' => array('class' => 'form-control'),
            ));
        }

        $builder->add( 'description', TextareaType::class, array(
            'label'=>'Description:',
            'required'=>false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('geoLocation', FellAppGeoLocationType::class, array(
            'form_custom_value' => $this->params,
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\GeoLocation',
            'label' => false,
            'required' => false
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Training',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_training';
    }
}
