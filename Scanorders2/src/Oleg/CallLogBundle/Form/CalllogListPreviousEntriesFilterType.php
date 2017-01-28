<?php

namespace Oleg\CallLogBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalllogListPreviousEntriesFilterType extends AbstractType
{

    private $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $messageCategoryParams = array(
            'label' => false,
            'required' => false,
            'choices' => $this->params['messageCategories'],
            'attr' => array('class' => 'combobox filter-message-category submit-on-enter-field', 'placeholder' => "Message Type"),
        );

        if( $this->params['messageCategory'] ) {
            $messageCategoryParams['data'] = $this->params['messageCategory'];
        }

        $builder->add('messageCategory', 'choice', $messageCategoryParams);

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'filter';
    }
}
