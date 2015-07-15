<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\AttachmentContainerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     

        //name
        $builder->add('blockname', 'collection', array(
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
        $builder->add('sectionsource', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Section Source:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__blocksectionsource__',
        ));

        $builder->add('slide', 'collection', array(
            'type' => new SlideType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => "Slide:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slide__',
        ));

        $builder->add('specialStains', 'collection', array(
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
            $builder->add('attachmentContainer', new AttachmentContainerType($params), array(
                'required' => false,
                'label' => false
            ));
        }

        //messages
        if( array_key_exists('datastructure',$this->params) && $this->params['datastructure'] == 'datastructure' ) {
            $builder->add('message', 'collection', array(
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
