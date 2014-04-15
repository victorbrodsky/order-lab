<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/14/14
 * Time: 1:09 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;

class CourseTitleListType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'name', 'custom_selector', array(
            'label' => 'Course Title:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-research-courseTitle', 'type' => 'hidden'),
            'classtype' => 'courseTitle'
        ));

        $builder->add( 'lessonTitles', 'custom_selector', array(
            'label' => 'Lesson Title:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width combobox-research-lessonTitle', 'type' => 'hidden'),
            'classtype' => 'lessonTitles'
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\CourseTitleList'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_coursetitlelisttype';
    }
}
