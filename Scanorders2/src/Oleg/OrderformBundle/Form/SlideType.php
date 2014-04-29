<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

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

        //TODO: replace by generic form type
        //$builder->add( 'stain', new StainType(), array('label'=>'Stain:') ); \
        $builder->add('stain', 'collection', array(
            'type' => new StainType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => " ",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slidestain__',
        ));

        $builder->add('scan', 'collection', array(
            'type' => new ScanType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => " ",
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

        $builder->add('specialStains', 'collection', array(
            'type' => new SpecialStainsType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'label' => false,   //"Special Stain Results:",
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__slidespecialstains__',
        ));

        //relevantScans
        $gen_attr = array('label'=>'Link(s) to related image(s)','class'=>'Oleg\OrderformBundle\Entity\RelevantScans','type'=>null);    //type=null => auto type
        $builder->add('relevantScans', 'collection', array(
            'type' => new GenericFieldType($this->params, null, $gen_attr),
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
            'label'=>'* Slide Type:',
            'required' => true,
            'attr' => $attr,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->where('s.type = :type')
                    ->setParameter('type', 'default')
                    ->orderBy('s.id', 'ASC');
            },
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Slide'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_slidetype';
    }
}
