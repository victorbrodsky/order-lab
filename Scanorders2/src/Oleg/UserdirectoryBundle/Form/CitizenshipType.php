<?php

namespace Oleg\UserdirectoryBundle\Form;

use Oleg\UserdirectoryBundle\Entity\Identifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CitizenshipType extends AbstractType
{

    protected $params;
    protected $rolePlatformAdmin;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'country', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Countries',
            'property' => 'name',
            'label'=>'Country of Citizenship:',
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

        //Visa status
        $builder->add('visa', null, array(
            'label' => 'Visa Status:',
            'attr' => array('class'=>'form-control')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Citizenship',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_citizenship';
    }
}
