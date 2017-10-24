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
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/16/14
 * Time: 9:55 AM
 */

namespace Oleg\UserdirectoryBundle\Services;

use FOS\CommentBundle\Events;
use FOS\CommentBundle\Event\CommentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FosCommentListener implements EventSubscriberInterface {


    private $container;
    private $em;
    protected $secTokenStorage;

    protected $secAuth;

    public function __construct( $container, $secTokenStorage, $em )
    {
        $this->container = $container;
        $this->em = $em;

        $this->secTokenStorage = $secTokenStorage;  //$container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::COMMENT_PRE_PERSIST => 'onCommentPrePersistTest',
        );
    }

    public function onCommentPrePersist(CommentEvent $event)
    {
        $comment = $event->getComment();

        //$user = $this->secTokenStorage->getToken()->getUser();

        if( $this->secTokenStorage->getToken() ) {
            $user = $this->secTokenStorage->getToken()->getUser();
            $comment->setAuthor($user);
        } else {
            $comment->setBody("NO TOKEN");
        }
    }

} 