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


use App\UserdirectoryBundle\Entity\Roles;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ImportUsersType extends AbstractType {


    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('file', FileType::class, array(
            'label' => 'Excel file:',
            //'attr' => array('class'=>'form-control')
        ));

        $builder->add('roles', EntityType::class, array(
            'label' => 'Roles to add to the processed users:',
            'class' => Roles::class,
            'choice_label' => function($role) {
                //return $role->getAlias() ? $role->getAlias()." (".$role->getName().")" : $role->getName();
                return $role->getAlias() ? $role->getAlias() : $role->getName();
            },
            'multiple' => true,
            'required' => false,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('roles')
                    ->where("roles.type IN ('default','user-added')")
                    ->orderBy('roles.orderinlist','ASC');
            },
            'attr' => array('class'=>'combobox')
        ));

        $builder->add('submit', SubmitType::class, array(
            'label' => 'Process Users',
            'attr' => array('class'=>'btn btn-info')
        ));

    }


    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return '';
        //return 'oleg_userdirectorybundle_import';
    }

} 