<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PerSiteSettingsType extends AbstractType
{

    protected $user;
    protected $roleAdmin;

    public function __construct( $user, $roleAdmin )
    {
        $this->user = $user;
        $this->roleAdmin = $roleAdmin;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if( $this->roleAdmin ) {

            $builder->add( 'permittedInstitutionalPHIScope', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:Institution',
                'property' => 'name',
                'label'=>'Institutional PHI Scope:',
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

            $builder->add( 'scanOrdersServicesScope', null, array(
                'label'=>'Service(s) Scope:',
                'required'=>false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width')
            ));

            $builder->add( 'chiefServices', null, array(
                'label'=>'Chief of the following Service(s) for Scope:',
                'required'=>false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width')
            ));



        }

        $builder->add( 'defaultService', null, array(
            'label'=>'Default Service:',
            'required'=>false,
            'attr' => array('class'=>'combobox combobox-width')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PerSiteSettings',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_persitesettings';
    }
}
