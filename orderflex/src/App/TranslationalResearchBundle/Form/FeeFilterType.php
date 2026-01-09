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

namespace App\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeeFilterType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add('search', TextType::class, array(
            //'placeholder' => 'Search',
            //'max_length' => 200,
            'required' => false,
            'label' => false,
            'attr' => array(
                'class' => 'form-control form-control-modif limit-font-size submit-on-enter-field',
                'placeholder'=>"Search by name, description, catalog ..."),
        ));

        $builder->add('feeScheduleVersion', TextType::class, array(
            'required' => false,
            'label' => false,
            'attr' => array(
                'class' => 'form-control form-control-modif limit-font-size submit-on-enter-field',
                'placeholder'=>"Fee Schedule Version"),
        ));

//        if( isset($this->params['specialties']) ) {
//            $specialties = $this->params['specialties'];
//        } else {
//            $specialties = NULL;
//        }
        $builder->add('specialties', ChoiceType::class, array(
            'choices' => $this->params['specialties'],
            //'data' => array('default','user-added'),
            'multiple' => true,
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width', 'placeholder'=>"Orderable for specialties")
        ));
//        $builder->add( 'specialties', EntityType::class, array(
//            'class' => 'AppTranslationalResearchBundle:SpecialtyList',
//            'label' => false,
//            'choice_label' => 'name',
//            'required'=> false,
//            'multiple' => true,
//            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'orderable-for-specialty';
    }
}
