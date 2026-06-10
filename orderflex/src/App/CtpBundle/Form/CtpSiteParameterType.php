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

namespace App\CtpBundle\Form;

use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CtpSiteParameterType extends AbstractType
{
    protected $params;

    public function formConstructor($params = null)
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('ctpLogos', CollectionType::class, [
            'entry_type' => DocumentType::class,
            'label' => 'Site logo image:',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ]);

        $builder->add('logoTopText', null, [
            'label' => 'Site logo text top line:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('logoBottomText', null, [
            'label' => 'Site logo text bottom line:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('logoTopTextColor', null, [
            'label' => 'Site logo text top line color:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('logoBottomTextColor', null, [
            'label' => 'Site logo text bottom line color:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('navbarSiteTitle', null, [
            'label' => 'Site title:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('navbarButtonTitle', null, [
            'label' => 'Navigation bar applications button title:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('footerInstLinkText', null, [
            'label' => 'Footer institution link text:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('footerInstLink', null, [
            'label' => 'Footer institution link URL:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('footerDepLinkText', null, [
            'label' => 'Footer department link text:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        $builder->add('footerDepLink', null, [
            'label' => 'Footer department link URL:',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ]);

        if( $this->params['cycle'] != 'show' ) {
            $builder->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\CtpBundle\Entity\CtpSiteParameter',
            'form_custom_value' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_ctpbundle_ctpsiteparameter';
    }
}
