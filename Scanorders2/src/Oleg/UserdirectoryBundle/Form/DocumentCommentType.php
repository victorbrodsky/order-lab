<?php

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Entity\PrivateComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class DocumentCommentType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $label = "Comment:";

        if( array_key_exists('documentContainer.comments.comment.label', $this->params) &&  $this->params['documentContainer.comments.comment.label'] != "") {
            $label = $this->params['documentContainer.comments.comment.label'] . " " . $label;
        }

        $builder->add( 'comment', 'textarea', array(
            'label' => $label,
            'required'=>false,
            'attr' => array('class' => 'textarea form-control')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentComment',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_documentcommenttype';
    }
}
