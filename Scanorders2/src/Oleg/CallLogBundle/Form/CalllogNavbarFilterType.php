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

namespace Oleg\CallLogBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalllogNavbarFilterType extends AbstractType
{

    private $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //searchtype
        $searchTypeArray = array(
            'label' => false,
            'required' => true,
            'choices' => $this->params['navbarSearchTypes'], //flipped
            //'choices_as_values' => true,
            //'attr' => array('class' => 'combobox111 combobox-no-width submit-on-enter-field', 'style'=>'border: 1px solid #ccc; border-radius: 4px 0 0 4px; height: 29px;'),
            //'attr' => array('class' => 'combobox combobox-no-width submit-on-enter-field', 'style'=>"width: 100px;"),
            'attr' => array('class' => 'combobox combobox-no-width submit-on-enter-field'),
        );
        if( array_key_exists('calllogsearchtype',$this->params) && $this->params['calllogsearchtype'] ) {
            $searchTypeArray['data'] = $this->params['calllogsearchtype'];
        }
        $builder->add('searchtype', ChoiceType::class, $searchTypeArray); //flipped

        //search
        $searchArray = array(
            'required'=>false,
            'label' => false,
            //'attr' => array('class'=>'form-control submit-on-enter-field', 'style'=>"height:30px; border-radius: 0 6px 6px 0"),
            'attr' => array('class'=>'form-control submit-on-enter-field', 'style'=>"border-radius: 4px; width: 125px;"),
            //'attr' => array('class'=>'form-control submit-on-enter-field', 'style'=>"border-radius: 4px;"),
        );
        if( array_key_exists('calllogsearch',$this->params) && $this->params['calllogsearch'] ) {
            $searchArray['data'] = $this->params['calllogsearch'];
        }
        $builder->add('search', TextType::class, $searchArray);

//        //metaphone
//        $mateaphoneArr = array(
//            'label' => "Search similar-sounding names on the whole page:",
//            'required' => false,
//            //'empty_data' => $this->params['metaphone'],
//            //'data' => $this->params['metaphone'],
//            //'attr' => array('class'=>'navbar-search-metaphone', 'style'=>'margin:0; width: 20px; display: none;')
//            'attr' => array('class' => 'navbar-search-metaphone', 'style' => 'margin:0; width: 20px;')
//        );
//        $builder->add('metaphone', 'checkbox', $mateaphoneArr);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'search';
    }
}
