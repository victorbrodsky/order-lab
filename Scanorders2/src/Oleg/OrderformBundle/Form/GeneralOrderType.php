<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Oleg\OrderformBundle\Entity\ProcedureList;

class GeneralOrderType extends AbstractType
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

        $builder->add('field',null,array(
            'required' => false,
            'label'=>$this->params['name'] . ' Order ID:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('source', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'label' => $this->params['name'] . ' Order Source:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    $whereArr = array();
                    foreach( $this->params['sources'] as $source ) {
                        $whereArr[] = "list.name = '".$source."'";
                    }
                    $where = implode('OR',$whereArr);
                    return $er->createQueryBuilder('list')
                        ->where($where)
                        ->orderBy("list.orderinlist","ASC");

                },
        ));


        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => $this->params['dataClass'],
            'label' => false,
            'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->params['dataClass'],
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_' . $this->params['typename'];
    }
}
