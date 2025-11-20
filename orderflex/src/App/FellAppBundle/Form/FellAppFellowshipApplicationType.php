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



use App\UserdirectoryBundle\Entity\FellowshipSubspecialty; //process.py script: replaced namespace by ::class: added use line for classname=FellowshipSubspecialty

use App\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\UserdirectoryBundle\Entity\Training;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FellAppFellowshipApplicationType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'fellowshipsubspecialtytype', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            'class' => FellowshipSubspecialty::class,
            'label'=> "Fellowship Specialty:",
            'required'=> false,
            //'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                ));
            },
        ));

        $builder->add('save', SubmitType::class,
            array(
                'label' => 'Add a New Fellowship Application Type',
                'attr' => array('class'=>'btn btn-primary'),
            )
        );

    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(array(
            //'data_class' => 'App\UserdirectoryBundle\Entity\Training',
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_fellappbundle_fellappfellowshipapplicationtype';
    }
}
