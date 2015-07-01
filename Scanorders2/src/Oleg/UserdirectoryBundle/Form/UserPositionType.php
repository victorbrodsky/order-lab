<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class UserPositionType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //hidden: set by js
        $builder->add( 'institution', null, array(
            'label' => false,
            'required'=> false,
        ));

        //hidden: set by js
        $builder->add( 'user', null, array(
            'label' => false,
            'required'=> false,
        ));

        //visible as positionType combobox attached to an institution node
        $builder->add( 'positionTypes', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:PositionTypeList',
            'property' => 'name',
            'label'=>'Position Type:',
            'required'=> false,
            'multiple' => true,
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


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserPosition',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_userposition';
    }
}
