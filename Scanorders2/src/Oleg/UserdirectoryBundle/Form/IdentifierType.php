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
    protected $rolePlatformAdmin;

    public function __construct( $params=null )
    {
        $this->params = $params;

        //only the "Platform Administrator" and "Deputy Platform Administrator" should be able to confirm the MRN by setting the Status of the MRN identifier as "Reviewed by Administration"
        if( $this->params['sc']->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $this->rolePlatformAdmin = true;
        } else {
            $this->rolePlatformAdmin = false;

        }
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
            'attr' => array('class'=>'form-control identifier-field-field')
        ));

        $builder->add('link', null, array(
            'label' => 'Link:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('publiclyVisible', 'checkbox', array(
            'required' => false,
            'label' => 'Publicly visible:',
        ));

        //status
        $baseUserAttr = new Identifier();
        $builder->add('status', 'choice', array(
            'disabled' => ($this->rolePlatformAdmin ? false : true),
            'choices'   => array(
                $baseUserAttr::STATUS_UNVERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED),
                $baseUserAttr::STATUS_VERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED)
            ),
            'label' => "Status:",
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width'),
        ));

        //keytypemrn
        $builder->add('keytypemrn', 'entity', array(
            'class' => 'OlegOrderformBundle:MrnType',
            'property' => 'name',
            'label'=>'MRN Type:',
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width identifier-keytypemrn-field'),
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
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Identifier',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_identifier';
    }
}
