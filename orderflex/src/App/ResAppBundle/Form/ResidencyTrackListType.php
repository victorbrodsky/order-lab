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

namespace App\ResAppBundle\Form;



use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ResidencyTrackListType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('coordinators', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Coordinator(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('user')
                    ->leftJoin("user.infos", "infos")
                    ->leftJoin("user.preferences", "preferences")
                    ->leftJoin("user.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("preferences.hide IS NULL OR preferences.hide=false")
                    ->orderBy("user.username", "ASC");
            }
        ));


        $builder->add('directors', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Director(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos", "infos")
                        ->leftJoin("user.preferences", "preferences")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                        ->andWhere("preferences.hide IS NULL OR preferences.hide=false")
                        ->orderBy("user.username", "ASC");
                }
        ));

        $builder->add('interviewers', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Default Interviewer(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos", "infos")
                        ->leftJoin("user.preferences", "preferences")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                        ->andWhere("preferences.hide IS NULL OR preferences.hide=false")
                        ->orderBy("user.username", "ASC");
                }
        ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\ResidencyTrackList',
            //'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_resappbundle_residencytracklist';
    }
}
