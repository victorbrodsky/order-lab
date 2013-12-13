<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oleg\OrderformBundle\Helper\FormHelper;

class BlockNameType extends AbstractType
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

        if( $this->params['type'] == 'singleorder') {
            $label = false;
        } else {
            $label = 'Block Name';
        }

        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
            $attr = array('class' => 'ajax-combobox-blockname keyfield', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'form-control form-control-modif');    //show
        }
        $builder->add('field', 'custom_selector', array(
            'label' => $label,
            'attr' => $attr,
            'required'=>false,
            'classtype' => 'blockname'
        ));

        $builder->add('blocknameothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockBlockname',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockBlockname',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_blocknametype';
    }
}
