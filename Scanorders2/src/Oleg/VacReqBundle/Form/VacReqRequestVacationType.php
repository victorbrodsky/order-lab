<?php

namespace Oleg\VacReqBundle\Form;


use Oleg\UserdirectoryBundle\Form\GeoLocationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class VacReqRequestVacationType extends VacReqRequestBaseType {

    public function __construct( $params=null, $entity = null )
    {
        parent::__construct($params,$entity);

        $this->requestTypeName = "Vacation";
        $this->numberOfDaysLabelPrefix = "Vacation Days Requested";
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder,$options);
    }



    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestVacation',
        ));
    }

    public function getName()
    {
        return 'oleg_vacreqbundle_request_vacation';
    }
}
