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

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Form\DataTransformer;

use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Entity\UserWrapper;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

//used by user type
class SingleUserWrapperTransformer extends UserWrapperTransformer//implements DataTransformerInterface
{


    /**
     * Use for 'Show'
     *
     * Transforms an UserWrapper object to a string.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform( $entity )
    {
        //echo "transform: entity=".$entity."<br>";
        //if( $entity ) {
            //echo "transform: entity=".$entity->getId()."<br>";
        //}

        if( !$entity ) {
            //echo "return empty <br>";
            return null;
        }

        //echo "return ".$entities->first()->getId()."<br>";

        //return User entity (if exists in UserWrapper), UserWrapper id (if id exists) or user string (UserWrapper->getFullName)
        $wrapperUser = $entity->getEntity();

        if( $wrapperUser instanceof User ) {
            //display user usning userInfos.displayName to match with the optimising version getProxyusersAction using userInfos.displayName
            return $wrapperUser->getDisplayName();
        }

        return $wrapperUser;
    }

    /**
     * Use for 'Submit'
     *
     * Transform a user (user id) or userstr (user string) to an UserWrapper object (i.e. user).
     *
     * @param  string $number
     *
     * @return $this->className|null
     *
     * @throws TransformationFailedException if object ($this->className) is not found.
     */
    public function reverseTransform($text)
    {

        //var_dump($text);
        //echo "<br>transformer: count=".count($text)."<br>";
        //echo "data transformer single text=".$text."<br>";

        if( !$text ) {
            //echo "return empty <br>";
            return null;
        }

        //$newListArr = $this->addSingleService( $newListArr, $text );
        //return $newListArr;

        $transformedEntity = null;
        $newListArr = new \Doctrine\Common\Collections\ArrayCollection();

        //echo "single text no array<br>";
        $newListArr = $this->addSingleObject( $newListArr, $text );
        $transformedEntity = $newListArr->first();

//        echo "transformedEntity id=".$transformedEntity->getId()."<br>";
//        echo "transformedEntity name=".$transformedEntity->getBlockPrefix()."<br>";
//        echo "transformedEntity user=".$transformedEntity->getUser()."<br>";
//        exit('1');

        return $transformedEntity; //UserWrapper
    }


    public function reverseTransformByType($text,$usernameType='UserWrapper') {

        if( !$text ) {
            return null;
        }

        $transformedEntity = null;
        $newListArr = new \Doctrine\Common\Collections\ArrayCollection();

        $newListArr = $this->addSingleObject( $newListArr, $text, $usernameType );
        $transformedEntity = $newListArr->first();

        return $transformedEntity; //UserWrapper
    }


}