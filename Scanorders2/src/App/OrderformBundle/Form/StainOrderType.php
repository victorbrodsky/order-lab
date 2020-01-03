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

use App\UserdirectoryBundle\Form\DocumentContainerType;
use App\UserdirectoryBundle\Form\DocumentType;
use App\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class StainOrderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        //Microscopic Image container
//        $params = array('labelPrefix'=>'Microscopic Image');
//        $equipmentTypes = array('Microscope Camera');
//        $params['device.types'] = $equipmentTypes;
//        $builder->add('documentContainer', new DocumentContainerType($params), array(
//            'data_class' => 'App\UserdirectoryBundle\Entity\DocumentContainer',
//            'label' => false
//        ));

        $params = array('labelPrefix'=>' for Histotechnologist');
        $builder->add('instruction', InstructionType::class, array(
            'data_class' => 'App\OrderformBundle\Entity\Instruction',
            'form_custom_value' => $params,
            'label' => false
        ));


//        $builder->add('imageMagnification', 'choice', array(
//            'label' => 'Microscopic Image Magnification:',
//            'choices' => array('100X', '83X', '60X', '40X', '20X', '10X', '4X', '2X'),
//            'required' => false,
//            'multiple' => false,
//            'expanded' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\StainOrder',
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_stainordertype';
    }
}
