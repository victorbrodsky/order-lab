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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalllogNavbarFilterType extends AbstractType
{

    private $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //searchtype
        $searchTypeArray = array(
            'label' => false,
            'required' => true,
            'choices' => $this->params['navbarSearchTypes'],
            'attr' => array('class' => 'combobox111 combobox-no-width submit-on-enter-field', 'style'=>'border: 1px solid #ccc; border-radius: 4px 0 0 4px; height: 29px;'),
        );
        if( array_key_exists('calllogsearchtype',$this->params) && $this->params['calllogsearchtype'] ) {
            $searchTypeArray['data'] = $this->params['calllogsearchtype'];
        }
        $builder->add('searchtype', 'choice', $searchTypeArray);

        //search
        $searchArray = array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'style'=>"height:30px; border-radius: 0 6px 6px 0"),
        );
        if( array_key_exists('calllogsearch',$this->params) && $this->params['calllogsearch'] ) {
            $searchArray['data'] = $this->params['calllogsearch'];
        }
        $builder->add('search', 'text', $searchArray);

        //metaphone
        $mateaphoneArr = array(
            'label' => "Search similar-sounding names:",
            'required' => false,
            //'empty_data' => $this->params['metaphone'],
            //'data' => $this->params['metaphone'],
            'attr' => array('class'=>'navbar-search-metaphone', 'style'=>'margin:0; width: 20px; display: none;')
        );
        $builder->add('metaphone', 'checkbox', $mateaphoneArr);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'search';
    }
}
