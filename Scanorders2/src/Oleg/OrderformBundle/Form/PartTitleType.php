<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PartTitleType extends AbstractType
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

        $builder->add('field', 'custom_selector', array(
            'label' => 'Part Title:',
            'attr' => array('class' => 'ajax-combobox ajax-combobox-parttitle', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'parttitle'
        ));


        $builder->add('others', new ArrayFieldType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartParttitle',
            'label' => false,
            'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PartParttitle',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_parttitletype';
    }
}
