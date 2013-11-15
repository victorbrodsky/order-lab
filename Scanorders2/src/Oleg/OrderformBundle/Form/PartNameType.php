<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oleg\OrderformBundle\Helper\FormHelper;

class PartNameType extends AbstractType
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

        $helper = new FormHelper();

        $attr = array('class' => 'combobox keyfield', 'style' => 'width:100%' );
        $builder->add('field', 'choice', array(
            'choices' => $helper->getPart(),
            'required' => false,
            'label' => 'Part Name',
            //'max_length' => '3',
            'attr' => $attr,
            //'empty_value' => "Choose an option",
            //'multiple' => false
        ));


        $builder->add('partnameothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartPartname',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartPartname',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_partnametype';
    }
}
