<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class StateLicenseType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'state', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:States',
            //'property' => 'name',
            'label'=>'State:',
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

        //country
        $preferredCountries = $this->params['em']->getRepository('OlegUserdirectoryBundle:Countries')->findByName(array('United States'));
        $builder->add( 'country', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Countries',
            'property' => 'name',
            'label'=>'Country:',
            'required'=> false,
            'multiple' => false,
            'preferred_choices' => $preferredCountries,
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

        $builder->add('licenseNumber', null, array(
            'label' => 'License Number:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('licenseIssuedDate', null, array(
            'label' => 'License Issued Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('licenseExpirationDate', null, array(
            'label' => 'License Expiration Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));


        $builder->add('active', 'checkbox', array(
            'label' => 'Active:',
            'attr' => array('class'=>'form-control')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\StateLicense',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_statelicense';
    }
}
