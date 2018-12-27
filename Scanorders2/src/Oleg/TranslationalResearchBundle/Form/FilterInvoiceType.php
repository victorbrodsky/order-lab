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

namespace Oleg\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterInvoiceType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('status', ChoiceType::class, array( //flipped
            'label' => false,
//            'choices' => array(
//                "Pending" => "Pending",
//                "Unpaid/Issued" => "Unpaid/Issued",
//                "Paid in Full" => "Paid in Full",
//                "Paid Partially" => "Paid Partially",
//                "Canceled" => "Canceled"
//            ),
            'choices' => $this->params['statuses'],
            'multiple' => true,
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Status")
        ));

        $builder->add('submitter', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => false,
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Submitter"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName", "ASC");
            },
        ));

        $builder->add('principalInvestigator', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> false,
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width', 'placeholder' => "Principal Investigator(s) for the project"),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add('billingContact', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> false,
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width', 'placeholder' => "Billing Contact for the project"),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add('salesperson', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => false,
            //'disabled' => true,
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => "Salesperson"),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName", "ASC");
            },
        ));

        $builder->add('idSearch', null, array(
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'form-control', 'placeholder' => "Invoice ID")
        ));

        $builder->add('totalMin', null, array(
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'form-control', 'placeholder' => "Total Amount Minimum")
        ));

        $builder->add('totalMax', null, array(
            'label' => false,
            'required' => false,
            'attr' => array('class' => 'form-control', 'placeholder' => "Total Amount Maximum")
        ));

        $builder->add('startDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'From Due Date'), //'title'=>'Start Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('endDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'To Due Date'), //'title'=>'End Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('startCreateDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'From Creation Date'),
        ));
        $builder->add('endCreateDate', DateTimeType::class, array(
            'label' => false,
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control submit-on-enter-field', 'placeholder'=>'To Creation Date'),
        ));

        $builder->add('version', ChoiceType::class, array(
            'label' => false,
            'choices' => $this->params['versions'],
            'required' => false,
            'attr' => array('class' => 'combobox', 'placeholder' => "Version")
        ));

        $builder->add('fundingNumber', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>'Fund Number'),
        ));

        $builder->add('fundingType',ChoiceType::class, array(
            'label' => false,
            'required' => false,
            'multiple' => false,
            'choices' => array(
                'Funded (With Fund Number)' => 'Funded',
                'Non-Funded (No Fund Number)' => 'Non-Funded'
            ),
            'attr' => array('class' => 'combobox', 'placeholder'=>'Funded vs Non-Funded'),
        ));

        $builder->add('irbNumber', TextType::class, array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'placeholder'=>$this->params['humanAnimalName'].' Number'),
        ));

//        $builder->add('complexFilterList', null, array(
//            'label' => false,
//            'required' => false,
//            'attr' => array('class' => 'form-control', 'placeholder' => "Optional Complex Filter")
//        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }
}
