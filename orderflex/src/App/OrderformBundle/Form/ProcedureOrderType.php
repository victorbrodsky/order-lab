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

namespace App\OrderformBundle\Form;

use App\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use App\UserdirectoryBundle\Form\DocumentContainerType;
use App\UserdirectoryBundle\Form\DocumentType;
use App\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ProcedureOrderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {

        $builder->add('type', ScanCustomSelectorType::class, array(
            'label' => 'Procedure Type:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox ajax-combobox-procedure', 'type' => 'hidden'),
            'classtype' => 'procedureType',
        ));

    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\ProcedureOrder',
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_orderformbundle_procedureordertype';
    }
}
