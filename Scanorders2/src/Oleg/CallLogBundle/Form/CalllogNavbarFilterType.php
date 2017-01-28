<?php

namespace Oleg\CallLogBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalllogNavbarFilterType extends AbstractType
{

    private $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $searchTypeArray = array(
            'label' => false,
            'required' => true,
            'choices' => $this->params['navbarSearchTypes'],
            'attr' => array('class' => 'combobox111 combobox-no-width submit-on-enter-field', 'style'=>'border: 1px solid #ccc; border-radius: 4px 0 0 4px; height: 29px;'),
        );
        if( $this->params['calllogsearchtype'] ) {
            $searchTypeArray['data'] = $this->params['calllogsearchtype'];
        }
        $builder->add('searchtype', 'choice', $searchTypeArray);

        $searchArray = array(
            'required'=>false,
            'label' => false,
            'attr' => array('class'=>'form-control submit-on-enter-field', 'style'=>"height:30px; border-radius: 0 6px 6px 0"),
        );
        if( $this->params['calllogsearch'] ) {
            $searchArray['data'] = $this->params['calllogsearch'];
        }
        $builder->add('search', 'text', $searchArray);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'search';
    }
}
