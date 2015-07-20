<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Oleg\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class BlockOrderType extends AbstractType
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

        $builder->add('processedDate', 'date', array(
            'label' => "Processed Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('processedByUser', null, array(
            'label' => 'Block Processed By:',
            'attr' => array('class' => 'combobox combobox-width'),
        ));


        //Block Image container
//        $params = array('labelPrefix'=>'Block Image');
//        $builder->add('documentContainer', new DocumentContainerType($params), array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentContainer',
//            'label' => false
//        ));

//        $params = array('labelPrefix'=>' for Embedder');
//        $builder->add('instruction', new InstructionType($params), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\InstructionList',
//            'label' => false
//        ));

        //EmbedderInstructionList
        $builder->add('embedderInstruction', 'custom_selector', array(
            'label' => 'Instructions for Embedder:',
            'attr' => array('class' => 'ajax-combobox-embedderinstruction', 'type' => 'hidden'),
            'required'=>true,
            'classtype' => 'embedderinstruction'
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockOrder',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_blockordertype';
    }
}
