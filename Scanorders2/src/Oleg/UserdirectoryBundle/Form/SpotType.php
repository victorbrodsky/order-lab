<?php

namespace Oleg\UserdirectoryBundle\Form;

use Oleg\UserdirectoryBundle\Form\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class SpotType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $builder->add('id',null,array(
//            'label' => "ID:",
//            'attr' => array('class'=>'form-control')
//        ));

        $currentUser = true;
        $cycle = $this->params['cycle'];
        $em = $this->params['em'];
        $roleAdmin = true;
        $read_only = false;

        $params = array('read_only'=>$read_only,'admin'=>$roleAdmin,'currentUser'=>$currentUser,'cycle'=>$cycle,'em'=>$em,'institution'=>false);

        $builder->add('currentLocation', new LocationType($params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Location',
            'label' => false,
        ));


//        $builder->add( 'mrnType', 'entity', array(
//            'class' => 'OlegOrderformBundle:MrnType',
//            'property' => 'name',
//            'label' => "Patient's MRN Type:",
//            'required'=> false,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.type = :typedef OR list.type = :typeadd")
//                        ->orderBy("list.orderinlist","ASC")
//                        ->setParameters( array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                        ));
//                },
//        ));
//
//        $builder->add('mrn',null,array(
//            'label' => "Patient's MRN:",
//            'attr' => array('class'=>'form-control')
//        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Spot',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_spottypetype';
    }
}
