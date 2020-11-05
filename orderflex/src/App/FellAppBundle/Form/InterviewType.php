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

namespace App\FellAppBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class InterviewType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        if( !array_key_exists('showFull', $this->params) ) {
            $this->params['showFull'] = true;
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        if( $this->params['showFull'] ) {

            $builder->add('interviewer', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => "Interviewer:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('user')
                            ->leftJoin("user.infos", "infos")
                            ->leftJoin("user.preferences", "preferences")
                            ->leftJoin("user.employmentStatus", "employmentStatus")
                            ->leftJoin("employmentStatus.employmentType", "employmentType")
                            ->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                            ->andWhere("preferences.hide IS NULL OR preferences.hide=false");
                            //->where('u.roles LIKE :role1 OR u.roles LIKE :role2')
                            //->setParameters(array('role1' => '%' . 'ROLE_FELLAPP_DIRECTOR' . '%', 'role2' => '%' . 'ROLE_FELLAPP_INTERVIEWER' . '%'));
                    },
            ));

            $builder->add('interviewDate', DateType::class,array(
                'widget' => 'single_text',
                'label' => "Interview Date:",
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control interview-interviewDate'),
                'required' => false,
            ));

            $builder->add('startTime', TimeType::class, array(
                'input'  => 'datetime',
                'widget' => 'choice',
                'label'=>'Start Time:'
            ));

            $builder->add('endTime', TimeType::class, array(
                'input'  => 'datetime',
                'widget' => 'choice',
                'label'=>'End Time:'
            ));

            ///////////////// location //////////////////
            $builder->add('location',null, array(
                'label' => "Interview Location:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width interview-location'),
            ));
//            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//                $interview = $event->getData();
//                $form = $event->getForm();
//
//                $options = array(
//                    'label' => "Interview Location:",
//                    'class' => 'AppUserdirectoryBundle:Location',
//                    'required' => false,
//                    'attr' => array('class' => 'combobox combobox-width interview-location'),
//                );
//
//                if( $interview && $interview->getInterviewer() ) {
//
//                    $officeLocation = $interview->getInterviewer()->getMainLocation();
//                    if( $officeLocation ) {
//                        $options['data'] = $officeLocation; //this causes: "Entities passed to the choice field must be managed. Maybe persist them in the entity manager?"
//                    }
//
//                }
//                //$form->add('location','entity',$options);
//                $form->add('location',null,$options);
//            });
            ///////////////// EOF location //////////////////

        } //if showFull

        $builder->add('academicRank',null, array(
            'label' => 'Academic Score:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width interview-rank interview-academicRank'),
        ));

        $builder->add('personalityRank',null, array(
            'label' => 'Personality Score:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width interview-rank interview-personalityRank'),
        ));

        $builder->add('potentialRank',null, array(
            'label' => 'Overall Potential Score:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width interview-rank interview-potentialRank'),
        ));

        $builder->add('totalRank', TextType::class, array(
            'label' => 'Total Score:',
            'required' => false,
            //'disabled' => true,
            'attr' => array('class' => 'form-control interview-totalRank', 'readonly'=>true),
        ));

        $builder->add('comment',null,array(
            'required' => false,
            'label'=>"Comments:",
            'attr' => array('class'=>'textarea form-control interview-comment')
        ));

        $builder->add('languageProficiency',null, array(
            'label' => 'Language Proficiency:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width interview-languageProficiency'),
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\FellAppBundle\Entity\Interview',
            'form_custom_value' => null
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_fellappbundle_interview';
    }
}
