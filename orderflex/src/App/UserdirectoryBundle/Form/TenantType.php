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



use App\UserdirectoryBundle\Entity\LinkTypeList; //process.py script: replaced namespace by ::class: added use line for classname=LinkTypeList


use App\UserdirectoryBundle\Entity\TenantUrlList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class TenantType extends AbstractType
//class TenantType extends ListType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);
        $this->addCustomFields($builder);
    }

    /**
     * @return void
     */
    //public function buildForm(FormBuilderInterface $builder, array $options)
    public function addCustomFields($builder)
    {
        $builder->add('name',null,array(
            'label' => 'Tenant name (without spaces and special characters):',
            'required' => true,
            'attr' => array('class'=>'form-control', 'required'=>'required')
        ));

        $builder->add('orderinlist',null,array(
            'label' => 'Display Order:',
            'required' => true,
            'attr' => array('class'=>'form-control', 'required'=>'required')
        ));

        if(1) {
            $builder->add('databaseHost', null, array(
                'label' => 'Database Host:',
                //'disabled' => true,
                'attr' => array('class' => 'form-control')
            ));

//        $builder->add('databasePort',null,array(
//            'label' => 'Database Port:',
//            'attr' => array('class'=>'form-control')
//        ));

            $builder->add('databaseName', null, array(
                'label' => 'Database Name:',
                //'disabled' => true,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('databaseUser', null, array(
                'label' => 'Database User:',
                //'disabled' => true,
                'attr' => array('class' => 'form-control')
            ));

            $builder->add('databasePassword', null, array(
                'label' => 'Database Password:',
                //'disabled' => true,
                'attr' => array('class' => 'form-control')
            ));
        }

        $builder->add('showOnHomepage',null,array(
            'label' => 'Show on Homepage:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('enabled',null,array(
            'label' => 'Enabled:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('adminName',null,array(
            'label' => 'Platform Administrator Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('institutionTitle',null,array(
            'label' => 'Tenant Institution Title:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('departmentTitle',null,array(
            'label' => 'Tenant Department Title:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('billingAdminName',null,array(
            'label' => 'Billing Tenant Administrator Contact Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('billingAdminEmail',null,array(
            'label' => 'Billing Tenant Administrator Contact Email:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('operationalAdminName',null,array(
            'label' => 'Operational Tenant Administrator Contact Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('operationalAdminEmail',null,array(
            'label' => 'Operational Tenant Administrator Contact Email:',
            'attr' => array('class'=>'form-control')
        ));


        $builder->add('urlSlug',null,array(
            'label' => 'URL Slug:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('tenantPort',null,array(
            'label' => 'Tenant Port:',
            'attr' => array('class'=>'form-control')
        ));

//        $builder->add('primaryTenant',null,array(
//            'label' => 'Primary Tenant:',
//            'attr' => array('class'=>'form-control')
//        ));

        if(0) {
            $builder->add('tenantUrl', EntityType::class, array(
                'class' => TenantUrlList::class,
                'choice_label' => 'getTenantUrl',
                'label' => 'Tenant Url:',
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        $builder->add('matchSystem',null,array(
            'label' => "Tenant's data source:",
            //'mapped' => false,
            'disabled' => true,
            'attr' => array('class'=>'form-control')
        ));

    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\TenantList',
            'form_custom_value' => null
        ));
    }
    /**
     * @return void
     */
    public function configureOptions_new(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\TenantList',
            //'inherit_data' => true,
            //'form_custom_value' => null,
            //'form_custom_value_mapper' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_tenanttype';
    }
}
