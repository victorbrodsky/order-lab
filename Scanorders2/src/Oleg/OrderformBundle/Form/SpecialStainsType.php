<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Doctrine\ORM\EntityRepository;

class SpecialStainsType extends AbstractType
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

        //field
        $builder->add('field', 'textarea', array(
            'label' => 'Associated Special Stain Result',
            'required' => false,
            'attr' => array('class'=>'textarea form-control form-control-modif')
        ));

        //staintype
        $attr = array('class' => 'ajax-combobox-staintype', 'type' => 'hidden');
        $options = array(
            'label' => 'Associated Special Stain Name:',
            'required' => true,
            'attr' => $attr,
            'classtype' => 'staintype'
        );
        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') {
            $options['data'] = 1;
        }
        $builder->add('staintype', 'custom_selector', $options );

        //stainothers
        $builder->add('stainothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockSpecialStains',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\BlockSpecialStains'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_specialstainstype';
    }
}
