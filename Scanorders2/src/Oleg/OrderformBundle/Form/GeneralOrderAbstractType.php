<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class GeneralOrderAbstractType extends AbstractType
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

        //order type (FormType)
        $builder->add('type',null,array(
            'required' => false,
            'label'=>$this->params['name'] . ' Order Type:',
            'attr' => array('class'=>'combobox combobox-width')
        ));

        //order number
        $builder->add('ordernumber',null,array(
            'required' => false,
            'label'=>"Order's " . $this->params['name'] . ' ID:',
            'attr' => array('class'=>'form-control')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\GeneralOrder'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_generalorder';
    }
}
