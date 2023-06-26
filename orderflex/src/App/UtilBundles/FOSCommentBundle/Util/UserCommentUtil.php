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
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UtilBundles\FOSCommentBundle\Util;



use App\UtilBundles\FOSCommentBundle\Model\CommentInterface;
use App\UtilBundles\FOSCommentBundle\Model\ThreadInterface;
use App\UserdirectoryBundle\Entity\FosComment;
use App\UserdirectoryBundle\Entity\FosThread;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UserCommentUtil {

    protected $em;
    protected $container;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container=null ) {
        $this->em = $em;
        $this->container = $container;
    }


    public function findThreadById( $id ) {
        //$thread = $this->em->getRepository('AppUserdirectoryBundle:FosThread')->find($id);
        $thread = $this->em->getRepository(FosThread::class)->find($id);

        return $thread;
    }

    /**
     * Creates an empty comment thread instance.
     *
     * @param bool $id
     *
     * @return Thread
     */
    public function createThread($id = null)
    {
        //$class = $this->getClass();
        $thread = new FosThread();

        if (null !== $id) {
            $thread->setId($id);
        }

        //$event = new ThreadEvent($thread);
        //$this->dispatch($event, Events::THREAD_CREATE);

        return $thread;
    }

    /**
     * Persists a thread.
     *
     * @param ThreadInterface $thread
     */
    public function saveThread($thread)
    {
        $this->em->persist($thread);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function saveComment(CommentInterface $comment)
    {

        //$this->doSaveComment($comment);
        $this->em->persist($comment->getThread());
        $this->em->persist($comment);
        $this->em->flush();

        return true;
    }


    public function findCommentTreeByThread( $thread, $depth = null )
    {
        //echo "threadId=".$thread->getId()."<br>";

        //return $this->em->getRepository('AppUserdirectoryBundle:FosComment')->find(14576);

        //$repository = $this->em->getRepository('AppUserdirectoryBundle:FosComment');
        $repository = $this->em->getRepository(FosComment::class);

        $dql =  $repository->createQueryBuilder("comment");
        $dql->select('comment');
        $dql->leftJoin("comment.thread", "thread");
        $dql->where("thread.id = :threadId");
        $dql->orderBy("comment.id",'DESC');
        $query = $this->em->createQuery($dql);

        $query->setParameters(array('threadId'=>$thread->getId()));

        $comments = $query->getResult();

//        foreach($comments as $comment) {
//            echo $comment->getId().": comment=".$comment->getCommentShort()."<br>";
//        }

        return $comments;
    }

    /**
     * {@inheritdoc}
     */
    public function findCommentsByThread(ThreadInterface $thread, $depth = null)
    {

        //echo "threadId=".$thread->getId()."<br>";

        //$repository = $this->em->getRepository('AppUserdirectoryBundle:FosComment');
        $repository = $this->em->getRepository(FosComment::class);

//        $dql =  $repository->createQueryBuilder("comment");
//        $dql->select('comment');
//        $dql->leftJoin("comment.thread", "thread");
//        $dql->where("thread.id = :threadId");
//        $query = $this->em->createQuery($dql);
//
//        $query->setParameters(array('threadId'=>$thread->getId()));
//
//        $comments = $query->getResult();
//
//        return $comments;

        $qb = $repository
            ->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->where('t.id = :thread')
            ->orderBy('c.ancestors', 'ASC')
            ->setParameter('thread', $thread->getId());

        if (null !== $depth && $depth >= 0) {
            // Queries for an additional level so templates can determine
            // if the final 'depth' layer has children.

            $qb->andWhere('c.depth < :depth')
                ->setParameter('depth', $depth + 1);
        }

        $comments = $qb
            ->getQuery()
            ->execute();

        return $comments;
    }

    /**
     * {@inheritdoc}
     */
    public function createComment(ThreadInterface $thread, CommentInterface $parent = null)
    {
        $comment = new FosComment();
        $comment->setThread($thread);

        if (null !== $parent) {
            $comment->setParent($parent);
        }

        $comment->setCreatedAt(new \DateTime('now'));

        //$event = new CommentEvent($comment);
        //$this->dispatch($event, Events::COMMENT_CREATE);

        return $comment;
    }

    public function findCommentById($commentId) {
        if( !$commentId ) {
            return null;
        }
        //return $this->em->getRepository('AppUserdirectoryBundle:FosComment')->find($commentId);
        return $this->em->getRepository(FosComment::class)->find($commentId);
    }

}


