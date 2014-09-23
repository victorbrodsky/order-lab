<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class EmploymentStatusType extends AbstractType
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


//        $builder->add('id','hidden',array(
//            'label'=>false,
//        ));

        $builder->add('hireDate',null,array(
            'label'=>"Date of Hire:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control')
        ));

        $builder->add('terminationDate',null,array(
            'label'=>"Date of Termination:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control')
        ));

        $builder->add( 'terminationType', 'entity', array(
            'disabled' => ($this->params['read_only'] ? true : false),
            'class' => 'OlegUserdirectoryBundle:EmploymentTerminationType',
            'property' => 'name',
            'label'=>'Type of Termination:',
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));

        $builder->add('terminationReason', null, array(
            'label' => 'Reason for Termination:',
            'attr' => array('class'=>'textarea form-control')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\EmploymentStatus',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_employmentstatus';
    }
}
