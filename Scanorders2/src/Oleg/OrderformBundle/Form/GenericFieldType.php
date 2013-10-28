<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class GenericFieldType extends AbstractType
{

    protected $params;
    protected $entity;
    protected $label;
    protected $clazz;
    protected $attr;
    protected $genAttr;

    //public function __construct( $params=null, $entity = null, $label=null, $clazz=null, $attr=null )
    public function __construct( $params=null, $entity = null, $genAttr=null, $attr=null )
    {
        $this->params = $params;
        $this->entity = $entity;
        //$this->label = $label;
        //$this->clazz = $clazz;
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

        $builder->add('other'.preg_replace('/\s+/', '', $this->genAttr['label']), new ArrayFieldType(), array(
            'data_class' => $this->genAttr['class']
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
