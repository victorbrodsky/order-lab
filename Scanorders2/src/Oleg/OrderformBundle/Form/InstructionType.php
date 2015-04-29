<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class InstructionType extends AbstractType
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


        $builder->add('instruction', 'text', array(
            'label' => 'Instruction'.$this->params['labelPrefix'].':',
            'attr' => array('class' => 'textarea form-control'),
        ));

        $builder->add('createdate', 'date', array(
            'label' => 'Instruction'.$this->params['labelPrefix'].' On:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('creator',null,array(
            'label'=>'Instruction'.$this->params['labelPrefix'].' Author:',
            'required' => false,
            'attr' => array('class'=>'combobox combobox-width select2-list-creator', 'readonly'=>'readonly')
        ));


//        $builder->add('others', new ArrayFieldType(), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\InstructionList',
//            'label' => false,
//			'attr' => array('style'=>'display:none;')
//        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\InstructionList',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_instructionlisttype';
    }
}
