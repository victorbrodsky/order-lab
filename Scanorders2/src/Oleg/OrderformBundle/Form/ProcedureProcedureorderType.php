<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ProcedureProcedureorderType extends AbstractType
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

        $builder->add('source', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'label' => 'Procedure Order Source:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.name = 'WCMC Epic Ambulatory EMR' OR list.name = 'Written or oral referral'")
                        ->orderBy("list.orderinlist","ASC");

                },
        ));

        $builder->add('orderinfo', 'entity', array(
            'class' => 'OlegOrderformBundle:OrderInfo',
            'label' => 'Procedure Order ID:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'property' => 'oid',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->orderBy("list.oid","ASC");

            },
        ));


        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureProcedureorder',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ProcedureProcedureorder',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_procedureprocedureordertype';
    }
}
