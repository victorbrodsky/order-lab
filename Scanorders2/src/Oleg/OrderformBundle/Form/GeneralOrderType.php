<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;
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

        //general order field
        $builder->add('field', new GeneralOrderAbstractType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\GeneralOrder',
            'label' => false
        ));

        //array field source
        $builder->add('source', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'label' => $this->params['name'] . ' Order Source:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    $query = $er->createQueryBuilder('list')->orderBy("list.orderinlist","ASC");
                    if( count($this->params['sources']) > 0 ) {
                        $whereArr = array();
                        foreach( $this->params['sources'] as $source ) {
                            $whereArr[] = "list.name = '".$source."'";
                        }
                        $where = implode(' OR ',$whereArr);
                        $query->andWhere($where);
                    }
                    return $query;
                },
        ));

        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => $this->params['dataClass'],
            'label' => false,
            'attr' => array('style'=>'display:none;')
        ));

    }

    //public function configureOptions(OptionsResolver $resolver)   //setDefaultOptions OptionsResolverInterface
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->params['dataClass']
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_' . $this->params['typename'];
    }
}
