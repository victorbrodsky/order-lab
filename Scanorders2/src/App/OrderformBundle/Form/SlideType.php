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


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use App\OrderformBundle\Entity\Slide;

class SlideType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;

        if( !array_key_exists('show-tree-depth',$this->params) || !$this->params['show-tree-depth'] ) {
            $this->params['show-tree-depth'] = true; //show all levels
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add( 'id', HiddenType::class );

        $builder->add('stain', CollectionType::class, array(
            'entry_type' => StainType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slidestain__',
        ));

        //if X=8, show only the first 8 levels (patient + encounter + procedure + accession + part + block + slide + image)
        if( $this->params['show-tree-depth'] === true || intval($this->params['show-tree-depth']) >= 8 ) {
            $builder->add('scan', CollectionType::class, array(
                'entry_type' => ImagingType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__slidescan__',
            ));
        }
        
        $builder->add('microscopicdescr', TextareaType::class, array(
                //'max_length'=>10000,
                'required'=>false,
                'label'=>'Microscopic Description:',
                'attr' => array('class'=>'textarea form-control'),
        ));

        //relevantScans
        $builder->add('relevantScans', CollectionType::class, array(
            'entry_type' => RelevantScansType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__sliderelevantscans__',
        ));
        
        //$builder->add('barcode', 'text', array('max_length'=>200,'required'=>false));

        $builder->add('title', TextType::class, array(
            'required'=>false,
            'label'=>'Title:',
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $attr = array('class' => 'combobox combobox-width slidetype-combobox');
        $builder->add('slidetype', EntityType::class, array(
            'class' => 'AppOrderformBundle:SlideType',
            'label'=>'Slide Type:',
            'required' => true,
            'attr' => $attr,
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


        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            //echo "slide datastructure <br>";

            $builder->add('message', CollectionType::class, array(
                'entry_type' => MessageObjectType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                    'form_custom_value_entity' => null
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__slidemessage__',
            ));

        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\OrderformBundle\Entity\Slide',
            'form_custom_value' => null
//            'empty_data' => function (FormInterface $form) {
//                    return new Slide(true,'valid',null,'111');
//            }
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_slidetype';
    }
}
