<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/14/14
 * Time: 1:09 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Form;

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

        $directors = $this->entity->getCourseTitle()->getDirectors();

        //create array of choices: 'choices' => array("OPTION1"=>"TEXT1", "OPTION2"=>"TEXT2", "OPTION3"=>"TEXT3"),
        $directorArr = array();
        foreach( $directors as $director ) {
            //echo $director."<br>";
            $directorArr[$director->getId()] = $director->getName();
        }

        $comment = '';
        if( $this->entity->getPrimarySet() ) {
            $comment = ' for this order';
        }

        $builder->add('primaryDirector', 'choice', array(
            'required' => true,
            'label'=>'Primary Course Director (as entered by user'.$comment.'):',
            'attr' => array('class' => 'combobox combobox-width'),
            'choices' => $directorArr,
        ));

        $builder->add('directors', 'collection', array(
            'type' => new DirectorType($this->params,$this->entity),
            'required' => false,
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\CourseTitleList'
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_coursetitlelisttype';
    }
}
