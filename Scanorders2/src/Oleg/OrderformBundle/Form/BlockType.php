<?php

namespace Oleg\OrderformBundle\Form;

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
            'label' => "Block Name:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__blockblockname__',
        ));

        $gen_attr = array('label'=>'Section Source','class'=>'Oleg\OrderformBundle\Entity\BlockSectionsource','type'=>null);    //type=null => auto type
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

        if( $this->params['type'] != 'single' ) {
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
        }


//        $factory  = $builder->getFormFactory();
//        $builder->addEventListener( FormEvents::PRE_SET_DATA, function(FormEvent $event) use($factory){
//
//                $form = $event->getForm();
//                $data = $event->getData();
//
//                //echo "class=".get_class($data)."<br>";
//                //echo "parent=".get_parent_class($data)."<br>";
//
//                //if( $data instanceof Stain ) {
//                if( get_parent_class($data) == 'Oleg\OrderformBundle\Entity\Block' || get_class($data) == 'Oleg\OrderformBundle\Entity\Block' ) {
//                    $name = $data->getName();
//                    //echo $data;
//
//                    $helper = new FormHelper();
//                    $arr = $helper->getBlock();
//
//                    $param = array(
//                        'label'=>'Block Name:',
//                        'max_length'=>'3',
//                        'choices' => $arr,
//                        'required'=> true,
//                        'attr' => array('class' => 'combobox', 'style' => 'width:70px'),
//                        'auto_initialize' => false,
//                    );
//
//                    $counter = 0;
//                    foreach( $arr as $var ){
//                        //echo "<br>".$var."?".$name;
//                        if( trim( $var ) == trim( $name ) ){
//                            $key = $counter;
//                            //echo " key=".$key;
//                            $param['data'] = $key;
//                        }
//                        $counter++;
//                    }
//
//                    // field name, field type, data, options
//                    $form->add(
//                        $factory->createNamed(
//                            'name',
//                            'choice',
//                            null,
//                            $param
//                        ));
//                }
//
//            }
//        );
        
        
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
