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

use Oleg\UserdirectoryBundle\Form\AttachmentContainerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class BlockType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        if( !array_key_exists('show-tree-depth',$this->params) || !$this->params['show-tree-depth'] ) {
            $this->params['show-tree-depth'] = true; //show all levels
        }
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     

        //name
        $builder->add('blockname', CollectionType::class, array(
            'type' => new BlockNameType($this->params, null),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Block ID:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__blockblockname__',
        ));

        $gen_attr = array('label'=>'Section Source:','class'=>'Oleg\OrderformBundle\Entity\BlockSectionsource','type'=>null);    //type=null => auto type
        $builder->add('sectionsource', CollectionType::class, array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Section Source:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__blocksectionsource__',
        ));

        //if X=7, show only the first 7 levels (patient + encounter + procedure + accession + part + block + slide)
        if( $this->params['show-tree-depth'] === true || intval($this->params['show-tree-depth']) >= 7 ) {
            $builder->add('slide', CollectionType::class, array(
                'type' => new SlideType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => "Slide:",
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__slide__',
            ));
        }

        $builder->add('specialStains', CollectionType::class, array(
            'type' => new SpecialStainsType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__blockspecialstains__',
        ));

        //extra data-structure fields
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $params = array('labelPrefix'=>'Block Image');
            $equipmentTypes = array('Block Imaging Camera');
            $params['device.types'] = $equipmentTypes;
            $builder->add('attachmentContainer', AttachmentContainerType::class, array(
                'form_custom_value' => $params,
                'required' => false,
                'label' => false
            ));
        }

        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $builder->add('message', CollectionType::class, array(
                'type' => new MessageObjectType($this->params),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'label' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__blockmessage__',
            ));
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Block'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_blocktype';
    }
}
