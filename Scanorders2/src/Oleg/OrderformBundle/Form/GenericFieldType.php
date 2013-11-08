<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class GenericFieldType extends AbstractType
{

    protected $params;
    protected $entity;
    protected $attr;
    protected $genAttr;

    public function __construct( $params=null, $entity = null, $genAttr=null, $attr=null )
    {
        $this->params = $params;
        $this->entity = $entity;
        $this->attr = $attr;
        $this->genAttr = $genAttr;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( !$this->attr ) {
            $attr = array('class'=>'form-control');
        } else {
            $attr = $this->attr;
        }

        $builder->add('field', $this->genAttr['type'], array(
            'label' => $this->genAttr['label'],
            'required' => false,
            'attr' =>$attr
        ));

        $builder->add('other', new ArrayFieldType(), array(
            'data_class' => $this->genAttr['class'],
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->genAttr['class'],
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_genfieldtype'; //generic field type
    }
}
