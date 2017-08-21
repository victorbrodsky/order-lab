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

namespace Oleg\OrderformBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ExternalIdType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('sourceSystem', EntityType::class, array(
            'label' => "External ID Source System:",
            'required'=> false,
            'multiple' => false,
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add('externalId', null, array(
            'label' => "External ID:",
            'required'=> false,
            'attr' => array('class' => 'form-control')
        ));


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ExternalId'
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_externalidtype';
    }
}
