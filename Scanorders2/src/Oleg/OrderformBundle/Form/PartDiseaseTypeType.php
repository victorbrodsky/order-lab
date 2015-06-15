<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oleg\OrderformBundle\Helper\FormHelper;

class PartDiseaseTypeType extends AbstractType
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

        $builder->add( 'diseaseTypes', 'entity', array(
            'class' => 'OlegOrderformBundle:DiseaseTypeList',
            'label'=>'Type of Disease:',
            'required'=>false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type diseaseType'), //'required' => '0', 'disabled'
            'choices' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->orderBy("list.orderinlist","ASC");
                },
        ));

        $builder->add( 'diseaseOrigins', 'entity', array(
            'class' => 'OlegOrderformBundle:DiseaseOriginList',
            'label'=>'Origin:',
            'required'=>false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type origin-checkboxes'), //'required' => '0', 'disabled'
            'choices' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->orderBy("list.orderinlist","ASC");
                },
        ));

        $builder->add('primaryOrgan', 'custom_selector', array(
            'label' => 'Primary Site of Origin:',
            'attr' => array('class' => 'ajax-combobox ajax-combobox-organ', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'sourceOrgan'
        ));

        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartDiseaseType',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartDiseaseType',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_partdiseasetypetype';
    }
}
