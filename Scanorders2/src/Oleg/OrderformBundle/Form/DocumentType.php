<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\OrderformBundle\Helper\FormHelper;

class DocumentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

         $builder->add('file', 'file', array(
             'label'=>'Paper:',
             'required'=>false,
         ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Document'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_documenttype';
    }
}
