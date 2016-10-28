<?php

namespace Oleg\UserdirectoryBundle\Form;



use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class DocumentType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('id', 'hidden', array(
            'label' => false,
            'attr' => array('class' => 'file-upload-id')
        ));

        //use dummyprototypefield to get id and name prototype for adding new document
        $builder->add('dummyprototypefield', 'hidden', array(
            'mapped' => false,
            'disabled' => true,
            'label' => false,
            'attr' => array('class' => 'dummyprototypefield')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Document',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_documenttype';
    }
}
