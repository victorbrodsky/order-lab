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


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ImportUsersType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('file', FileType::class, array(
            'label' => 'Excel file:',
            //'attr' => array('class'=>'form-control')
        ));

        $builder->add('submit', SubmitType::class, array(
            'label' => 'Import Users',
            'attr' => array('class'=>'btn btn-info')
        ));

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return null;
        //return 'oleg_userdirectorybundle_import';
    }

} 