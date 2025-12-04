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


use App\UserdirectoryBundle\Entity\Institution;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class FellowshipSubspecialtyType extends AbstractType
{

    protected $params;
    private $selectStr;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $this->selectStr = "employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL";
        if( $this->params['isHubServer'] == true ) {
            $this->selectStr = '';
        }
        $this->selectStr = '';

        $builder->add('coordinators', EntityType::class, array(
            'class' => User::class,
            'label' => "Coordinator(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                $qb = $er->createQueryBuilder('user')
                    ->leftJoin("user.infos", "infos")
                    ->leftJoin("user.preferences", "preferences")
                    ->leftJoin("user.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("infos.lastName NOT LIKE 'test%'")// AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    //->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere($this->selectStr)
                    ->andWhere("preferences.hide IS NULL OR preferences.hide=false")
                    ->orderBy("user.username", "ASC");

                if (!empty($this->selectStr)) {
                    $qb->andWhere($this->selectStr);
                }
                return $qb;
            }
        ));


        $builder->add('directors', EntityType::class, array(
            'class' => User::class,
            'label' => "Director(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                $qb = $er->createQueryBuilder('user')
                    ->leftJoin("user.infos", "infos")
                    ->leftJoin("user.preferences", "preferences")
                    ->leftJoin("user.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    //->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("preferences.hide IS NULL OR preferences.hide=false")
                    ->orderBy("user.username", "ASC");

                if (!empty($this->selectStr)) {
                    $qb->andWhere($this->selectStr);
                }
                return $qb;
            }
        ));

        $builder->add('interviewers', EntityType::class, array(
            'class' => User::class,
            'label' => "Default Interviewer(s):",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                $qb = $er->createQueryBuilder('user')
                    ->leftJoin("user.infos", "infos")
                    ->leftJoin("user.preferences", "preferences")
                    ->leftJoin("user.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    //->where("infos.lastName NOT LIKE 'test%' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                    ->andWhere("preferences.hide IS NULL OR preferences.hide=false")
                    ->orderBy("user.username", "ASC");

                if (!empty($this->selectStr)) {
                    $qb->andWhere($this->selectStr);
                }
                return $qb;
            }
        ));

        $builder->add('institution', EntityType::class, array(
            'class' => Institution::class,
            'label' => "Institution:",
            'choice_label' => "getTreeRootNameChildName",
            'required' => false,
            'choices' => $this->params['institutions'],
            //'invalid_message' => 'institution invalid value',
            'attr' => array('class' => 'combobox combobox-width fellapp-institution'),
        ));

    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(array(
            //'data_class' => 'App\UserdirectoryBundle\Entity\FellowshipSubspecialty',
            'data_class' => null,
            'form_custom_value' => null,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_fellappbundle_fellowshipSubspecialty';
    }
}
