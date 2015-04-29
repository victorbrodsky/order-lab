<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\UserdirectoryBundle\Form\DocumentContainerType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Oleg\UserdirectoryBundle\Form\UserWrapperType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class StainOrderType extends AbstractType
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
            'label' => "Slide Stained On:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('processedByUser', null, array(
            'label' => 'Slide Stained By:',
            'attr' => array('class' => 'combobox combobox-width'),
        ));

//        //Microscopic Image container
//        $params = array('labelPrefix'=>'Microscopic Image');
//        $equipmentTypes = array('Microscope Camera');
//        $params['device.types'] = $equipmentTypes;
//        $builder->add('documentContainer', new DocumentContainerType($params), array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentContainer',
//            'label' => false
//        ));

        $params = array('labelPrefix'=>' for Slide Stainer');
        $builder->add('instruction', new InstructionType($params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\InstructionList',
            'label' => false
        ));


//        $builder->add('imageMagnification', 'choice', array(
//            'label' => 'Microscopic Image Magnification:',
//            'choices' => array('100X', '83X', '60X', '40X', '20X', '10X', '4X', '2X'),
//            'required' => false,
//            'multiple' => false,
//            'expanded' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\StainOrder',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_stainordertype';
    }
}
