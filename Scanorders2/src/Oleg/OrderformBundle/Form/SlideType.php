<?php

namespace Oleg\OrderformBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Oleg\OrderformBundle\Entity\Slide;

class SlideType extends AbstractType
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
        $builder->add( 'id', 'hidden' );

        $builder->add('stain', 'collection', array(
            'type' => new StainType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slidestain__',
        ));

        $builder->add('scan', 'collection', array(
            'type' => new ImagingType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slidescan__',
        ));
        
        $builder->add('microscopicdescr', 'textarea', array(
                'max_length'=>10000,
                'required'=>false,
                'label'=>'Microscopic Description:',
                'attr' => array('class'=>'textarea form-control'),
        ));

        //relevantScans
        $builder->add('relevantScans', 'collection', array(
            'type' => new RelevantScansType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__sliderelevantscans__',
        ));
        
        //$builder->add('barcode', 'text', array('max_length'=>200,'required'=>false));

        $builder->add('title', 'text', array(
            'required'=>false,
            'label'=>'Title:',
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $attr = array('class' => 'combobox combobox-width slidetype-combobox');
        $builder->add('slidetype', 'entity', array(
            'class' => 'OlegOrderformBundle:SlideType',
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

            $builder->add('message', 'collection', array(
                'type' => new MessageObjectType($this->params),
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Slide',
//            'empty_data' => function (FormInterface $form) {
//                    return new Slide(true,'valid',null,'111');
//            }
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_slidetype';
    }
}
