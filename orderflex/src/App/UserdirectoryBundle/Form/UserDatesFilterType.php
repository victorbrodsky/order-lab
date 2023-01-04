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

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDatesFilterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add('users', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label' => false,
            'required' => false,
            'multiple' => true,
            //'choice_label' => 'name',
            'attr' => array('class'=>'combobox combobox-width', 'placeholder'=>"Employee"),
            //'disabled' => true,    //$readOnly,   //($this->params['review'] ? true : false),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('user')
                    ->leftJoin("user.infos","infos")
                    ->leftJoin("user.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                    //->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->orderBy("infos.lastName","ASC");
            },
        ));

        $builder->add('search', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array(
                'class' => 'form-control form-control-modif limit-font-size submit-on-enter-field',
                'placeholder' => 'Free search (name, id, active directory account, email)'
            ),
        ));


        $builder->add('startdate', DateTimeType::class, array(
            'label' => false, //'Start Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'Employment start date')
        ));

        $builder->add('enddate', DateTimeType::class, array(
            'label' => false, //'End Date/Time:',
            'required' => false,
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'Employment end date')
        ));

        $builder->add('roles', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:Roles',
            'choice_label' => 'alias',
            'label' => false,
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Roles'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.name", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

        //status: Activated, Deactivated, Locked
        $builder->add('status', ChoiceType::class, array(
//            'choices'   => array(
//                'Active Account (Employed)' => 'active',
//                'Inactive Account (Not currently employed)' => 'inactive',
//                'Inactive Account (Locked)' => 'locked'
//            ),
            'choices'   => array(
                'Inactive institutional AD account while site access is not locked' => 'adinactive-not-locked',
                'Locked (no site access) while institutional AD account is active' => 'adactive-locked',
                'Active institutional AD account' => 'adactive',
                'Inactive institutional AD account' => 'adinactive',
                'Locked (no site access)' => 'locked',
                'Active Account' => 'active',
                'Ended employment' => 'terminated',
            ),
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Account status'),
        ));


        $builder->add('submit', SubmitType::class, array(
            'label' => 'Filter',
            'attr' => array('class' => 'btn btn-sm btn-default')
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }
}
