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

use App\UserdirectoryBundle\Entity\HostedUserGroupList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class HostedGroupHolderType extends AbstractType
{

    //Use user.administrativeTitles as an example

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('hostedUserGroup', EntityType::class, array(
            'class' => HostedUserGroupList::class,
            'choice_label' => 'getTenantUrl', //'getTreeName',
            'label' => 'Hosted User Group Type(s):',
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox'),
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

        $builder->add('databaseHost',null,array(
            'label' => "Database Host (default: localhost):",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('databasePort',null,array(
            'label' => "Database Port (default: 5432):",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('databaseName',null,array(
            'label' => "Database Name:",
            'required' => true,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('databaseUser',null,array(
            'label' => "Database User:",
            'required' => true,
            'attr' => array('class'=>'form-control', 'required'=>'required'),
        ));

        $builder->add('databasePassword',null,array(
            'label' => "Database Password:",
            'required' => true,
            'attr' => array('class'=>'form-control', 'required'=>'required'),
        ));

//        $builder->add('systemDb',null,array(
//            'label' => "System DB (Use as a system DB to store multitenancy parameters):",
//            'required' => true,
//            'attr' => array('class'=>'form-control'),
//        ));

        $builder->add('enabled',null,array(
            'label' => "Enabled:",
            'required' => true,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add('orderinlist',null,array(
            'label' => "Display Order:",
            'required' => true,
            'attr' => array('class'=>'form-control'),
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\HostedGroupHolder',
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_hostedgroupholder';
    }
}
