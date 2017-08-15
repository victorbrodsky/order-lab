<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class AccessionAccessionType extends AbstractType
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

        //accession number
        $attr = array(
            'class'=>'form-control form-control-modif keyfield accession-mask',
            //'title' => 'Example: S12-123456 or SS12-123456. Valid Accession#: A00-1 through ZZ99-999999',
        );

        if( $this->params['type'] == 'One-Slide Scan Order') {
            $attr['style'] = 'width:100%; height:27px';
            $accTypeLabel = "Accession Type:";
            //$gen_attr = array('label'=>false,'class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        } else {
            $accTypeLabel = false;
            //$gen_attr = array('label'=>'Accession Number [or Label]','class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        }

        $builder->add( 'field', 'text', array(
            'label'=>'Accession Number [or Label]:',
            'required'=>false,
            'attr' => $attr
        ));

        //accession type
        $attr = array('class' => 'ajax-combobox combobox combobox-width accessiontype-combobox', 'type' => 'hidden'); //combobox
        $options = array(
            'label' => $accTypeLabel,
            'required' => true,
            'attr' => $attr,
            'classtype' => 'accessiontype',
        );

        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') {
            $options['data'] = 1; //new
        }

        $builder->add('keytype', 'custom_selector', $options);


        $builder->add('others', new ArrayFieldType($this->params), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\AccessionAccession',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));



    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\AccessionAccession',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_accessionaccessiontype';
    }
}
