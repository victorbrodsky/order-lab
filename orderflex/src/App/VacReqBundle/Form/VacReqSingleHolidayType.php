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

namespace App\VacReqBundle\Form;

use App\VacReqBundle\Entity\VacReqHolidayList;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VacReqSingleHolidayType extends AbstractType
{

    private $params;


    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('observed', CheckboxType::class, array(
            'label' => false,
            'required' => false,
        ));

        //get only institutions related by vacreq
//        $builder->add('institutions', ChoiceType::class, array(
//            'label' => false,
//            'required' => false,
//            'multiple' => false,
//            'attr' => array('class' => 'combobox combobox-width', 'placeholder' => 'Organizational Group'),
//        ));

        $builder->add('institutions', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:Institution',
            'choice_label' => 'name',
            'label'=>'Instance maintained for the following institutions (Holiday\'s default institutions):',
            'required'=> false,
            'multiple' => true,
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

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            //'csrf_protection' => false,
            //'allow_extra_fields' => true,
            'data_class' => VacReqHolidayList::class,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'holiday';
    }
}
