<?php

namespace Oleg\UserdirectoryBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HierarchyFilterType extends AbstractType
{

    private $params;


    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $types = array(
            "default"=>"default",
            "user-added"=>"user-added",
            "disabled"=>"disabled",
            "draft"=>"draft"
        );

        $params = array(
            'label'=>'Types:',
            'choices' => $types,
            'required' => false,
            'multiple' => true,
            'attr' => array('class'=>'combobox select2-hierarchy-types') //submit-on-enter-field
        );

        if( $this->params && $this->params['types'] ) {
            $params['data'] = $this->params['types'];
        }

        $builder->add('types','choice',$params);

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'filter';
    }
}
