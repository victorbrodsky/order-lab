<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class IdentifierType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //service. User should be able to add institution to administrative or appointment titles
        $builder->add('keytype', 'employees_custom_selector', array(
            'label' => "Identifier Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-identifierkeytype', 'type' => 'hidden'),
            'classtype' => 'identifierkeytype'
        ));

        $builder->add('field', null, array(
            'label' => 'Identifier:',
            'attr' => array('class'=>'form-control')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Identifier',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_identifier';
    }
}
