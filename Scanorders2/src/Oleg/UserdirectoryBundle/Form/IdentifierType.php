<?php

namespace Oleg\UserdirectoryBundle\Form;

use Oleg\UserdirectoryBundle\Entity\Identifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class IdentifierType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

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

        $builder->add('link', null, array(
            'label' => 'Link:',
            'attr' => array('class'=>'form-control')
        ));

        //status
        $baseUserAttr = new Identifier();
        $builder->add('status', 'choice', array(
            'disabled' => ($this->params['admin'] ? false : true),
            'choices'   => array(
                $baseUserAttr::STATUS_UNVERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED),
                $baseUserAttr::STATUS_VERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED)
            ),
            'label' => "Status:",
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width'),
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
