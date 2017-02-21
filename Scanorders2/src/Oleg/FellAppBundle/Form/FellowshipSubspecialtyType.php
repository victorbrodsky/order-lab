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


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class FellowshipSubspecialtyType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('coordinators', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => "Coordinator(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('user')
                    ->leftJoin("user.infos", "infos")
                    ->leftJoin("user.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    ->orderBy("user.username", "ASC");
            }
        ));


        $builder->add('directors', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => "Director(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos", "infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                        ->orderBy("user.username", "ASC");
                }
        ));

        $builder->add('interviewers', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => "Default Interviewer(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos", "infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                        ->orderBy("user.username", "ASC");
                }
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\FellowshipSubspecialty',
            //'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'oleg_fellappbundle_fellowshipSubspecialty';
    }
}
