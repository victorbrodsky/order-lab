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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class GenericFieldType extends AbstractType
{

    protected $params;
    protected $attr;
    protected $genAttr;

    public function formConstructor( $params=null, $genAttr=null, $attr=null )
    {
        $this->params = $params;
        $this->attr = $attr;
        $this->genAttr = $genAttr;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_genAttr'],$options['form_custom_value_attr']);

        if( $this->attr == null ) {
            $attr = array('class'=>'form-control');
        } else {
            $attr = $this->attr;
        }

        if( $this->genAttr['type'] == "text" ) {
            $builder->add('field', TextType::class, array(
                'label' => $this->genAttr['label'],
                'required' => false,
                'attr' =>$attr
            ));
        } else {
            $builder->add('field', null, array(
                'label' => $this->genAttr['label'],
                'required' => false,
                'attr' =>$attr
            ));
        }
//        $builder->add('field', $this->genAttr['type'], array(
//            'label' => $this->genAttr['label'],
//            'required' => false,
//            'attr' =>$attr
//        ));

        $builder->add('others', ArrayFieldType::class, array(
            'data_class' => $this->genAttr['class'],
            'form_custom_value' => $this->params,
            'label' => false,
            'attr' => array('style'=>'display:none;')
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        if( $this->genAttr && isset($this->genAttr['class']) ) {
            //ok
        } else {
            $this->genAttr = array('class'=>null);
        }

        //dump($this->genAttr);
        //exit('class='.$this->genAttr['class']);

        $resolver->setDefaults(array(
            'data_class' => $this->genAttr['class'],
            'form_custom_value' => null,
            'form_custom_value_genAttr' => null,
            'form_custom_value_attr' => null,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_genfieldtype'; //generic field type
    }
}
