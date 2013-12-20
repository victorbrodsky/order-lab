<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Oleg\OrderformBundle\Entity\ProcedureList;

class ProcedureNameType extends AbstractType
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

        $attr = array('class' => 'ajax-combobox-procedure', 'type' => 'hidden');

        $builder->add('field', 'custom_selector', array(
            'label' => 'Procedure Type',
            'required' => false,
            'attr' => $attr,
            'classtype' => 'procedureType',
        ));


        $builder->add('procedureothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureName',
            'label' => false
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureName',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_nametype';
    }
}
