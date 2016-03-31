<?php

namespace Oleg\FellAppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class RankType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('rank',null, array(
            'label' => 'Rank:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\FellAppBundle\Entity\Rank',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'oleg_fellappbundle_rank';
    }
}
