<?php

namespace Oleg\VacReqBundle\Form;


use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;



class VacReqCarryOverType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('year', null, array(
            'label' => "Academic Year:",
            'attr' => array('class' => 'form-control'),
            'read_only' => true
        ));

        $builder->add('days', null, array(
            'label' => "Carry Over Days:",
            'attr' => array('class' => 'form-control'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqCarryOver',
            //'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'oleg_vacreqbundle_carryover';
    }
}
