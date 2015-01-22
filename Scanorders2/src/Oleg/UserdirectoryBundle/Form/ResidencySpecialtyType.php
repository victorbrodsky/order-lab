<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ResidencySpecialtyType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->add('boardCertificateAvailable', 'checkbox', array(
            'label' => 'Board Certificate Available:',
            'required'  => false
        ));

        $builder->add('name',null,array(
            'label'=>'Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('children', new FellowshipSubspecialtyType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\FellowshipSubspecialty',
            'label' => false
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\ResidencySpecialty',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_residencyspecialty';
    }
}
