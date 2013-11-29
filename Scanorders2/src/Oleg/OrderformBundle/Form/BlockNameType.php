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

//        $helper = new FormHelper();
//
//        $attr = array('class' => 'combobox keyfield', 'style' => 'width:100%' );
//        $builder->add('field', 'choice', array(
//            'choices' => $helper->getBlock(),
//            'required' => false,
//            'label' => 'Block Name',
//            'attr' => $attr,
//        ));
//
//        $builder->add('blocknameothers', new ArrayFieldType(), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\BlockBlockname',
//            'label' => false
//        ));


        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' ) {
            $attr = array('class' => 'ajax-combobox-blockname keyfield', 'type' => 'hidden');    //new
        } else {
            $attr = array('class' => 'form-control form-control-modif');    //show
        }
        $builder->add('field', 'custom_selector', array(
            'label' => 'Block Name:',   
            'attr' => $attr,
            'required'=>false,
            'classtype' => 'blockname'
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
