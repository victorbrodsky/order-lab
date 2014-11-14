<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ProcedureEncounterType extends AbstractType
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

        $builder->add('field', 'hidden', array('label'=>false));

        //$builder->add('keytype', 'hidden', array('label'=>false));
        $builder->add('keytype', 'entity', array(
            'class' => 'OlegOrderformBundle:EncounterType',
            'label'=>false,
            'required' => true,
            'data' => 1,
            'attr' => array('style'=>'display:none;'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->orderBy("list.orderinlist","ASC")
                        ->setMaxResults(1);

                },
        ));

        $builder->add('procedureencounterothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureEncounter',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureEncounter',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_procedureencountertype';
    }
}
