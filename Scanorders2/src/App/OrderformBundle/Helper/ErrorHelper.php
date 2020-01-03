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

namespace App\OrderformBundle\Helper;

/**
 * Description of ErrorHelper
 *
 * @author Cina
 */
class ErrorHelper {
    
    public function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();

        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                /**
                 * @var \Symfony\Component\Form\Form $child
                 */
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        } else {
            /**
             * @var \Symfony\Component\Form\FormError $error
             */
            foreach ($form->getErrors() as $key => $error) {
                $errors[] = $error->getMessage();
            }
        }

        return $errors;
    }
    
}

?>
