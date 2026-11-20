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

namespace App\WikiportalBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\WikiportalBundle\Entity\WikiPage;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalWikiPageController extends OrderAbstractController
{

    #[Route(path: '/pages', name: 'wikiportal_pages', methods: ['GET'])]
    #[Template('AppWikiportalBundle/Default/pages.html.twig')]
    public function pagesAction(Request $request)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_USER')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(WikiPage::class)->findBy(array(), array('title' => 'ASC'));

        return array(
            'sitename' => $this->getParameter('wikiportal.sitename'),
            'pages' => $pages
        );
    }

    #[Route(path: '/page/{slug}', name: 'wikiportal_page_show', methods: ['GET'])]
    #[Template('AppWikiportalBundle/Default/page-show.html.twig')]
    public function showPageAction(Request $request, $slug)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_USER')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(WikiPage::class)->findOneBySlug($slug);

        if (!$page) {
            throw $this->createNotFoundException('Wiki page not found: ' . $slug);
        }

        return array(
            'sitename' => $this->getParameter('wikiportal.sitename'),
            'page' => $page
        );
    }

    #[Route(path: '/page-new', name: 'wikiportal_page_new', methods: ['GET', 'POST'])]
    #[Template('AppWikiportalBundle/Default/page-edit.html.twig')]
    public function newPageAction(Request $request)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_EDITOR')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        if ($request->isMethod('POST')) {
            $page = new WikiPage($this->getUser());
            $res = $this->updatePageFromRequest($page, $request);
            if ($res !== true) {
                $this->addFlash('warning', $res);
                return array(
                    'sitename' => $this->getParameter('wikiportal.sitename'),
                    'page' => $page
                );
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $userSecUtil = $this->container->get('user_security_utility');
            $event = "New wiki page created: " . $page->getTitle() . " (id=" . $page->getId() . ")";
            $userSecUtil->createUserEditEvent($this->getParameter('wikiportal.sitename'), $event, $this->getUser(), $page, $request, 'Wiki Page Created');

            return $this->redirect($this->generateUrl('wikiportal_page_show', array('slug' => $page->getSlug())));
        }

        return array(
            'sitename' => $this->getParameter('wikiportal.sitename'),
            'page' => null
        );
    }

    #[Route(path: '/page-edit/{id}', name: 'wikiportal_page_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[Template('AppWikiportalBundle/Default/page-edit.html.twig')]
    public function editPageAction(Request $request, $id)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_EDITOR')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(WikiPage::class)->find($id);

        if (!$page) {
            throw $this->createNotFoundException('Wiki page not found: ' . $id);
        }

        if ($request->isMethod('POST')) {
            $res = $this->updatePageFromRequest($page, $request);
            if ($res !== true) {
                $this->addFlash('warning', $res);
                return array(
                    'sitename' => $this->getParameter('wikiportal.sitename'),
                    'page' => $page
                );
            }

            $page->setUpdateuser($this->getUser());
            $em->flush();

            $userSecUtil = $this->container->get('user_security_utility');
            $event = "Wiki page updated: " . $page->getTitle() . " (id=" . $page->getId() . ")";
            $userSecUtil->createUserEditEvent($this->getParameter('wikiportal.sitename'), $event, $this->getUser(), $page, $request, 'Wiki Page Updated');

            return $this->redirect($this->generateUrl('wikiportal_page_show', array('slug' => $page->getSlug())));
        }

        return array(
            'sitename' => $this->getParameter('wikiportal.sitename'),
            'page' => $page
        );
    }

    #[Route(path: '/page-delete/{id}', name: 'wikiportal_page_delete', methods: ['POST', 'DELETE'], requirements: ['id' => '\d+'])]
    public function deletePageAction(Request $request, $id)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_ADMIN')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(WikiPage::class)->find($id);

        if (!$page) {
            throw $this->createNotFoundException('Wiki page not found: ' . $id);
        }

        $title = $page->getTitle();
        $em->remove($page);
        $em->flush();

        $userSecUtil = $this->container->get('user_security_utility');
        $event = "Wiki page deleted: " . $title . " (id=" . $id . ")";
        $userSecUtil->createUserEditEvent($this->getParameter('wikiportal.sitename'), $event, $this->getUser(), null, $request, 'Wiki Page Deleted');

        $this->addFlash('notice', "Wiki page '" . $title . "' has been deleted.");
        return $this->redirect($this->generateUrl('wikiportal_pages'));
    }

    /**
     * Populate page fields from the request. Returns true or an error string.
     */
    private function updatePageFromRequest($page, Request $request)
    {
        $title = trim((string)$request->request->get('title'));
        $slug = trim((string)$request->request->get('slug'));
        $body = $request->request->get('body');

        if (!$title) {
            return 'Title is required.';
        }

        if (!$slug) {
            $slug = $this->slugify($title);
        }

        $em = $this->getDoctrine()->getManager();
        $existing = $em->getRepository(WikiPage::class)->findOneBySlug($slug);
        if ($existing && $existing->getId() != $page->getId()) {
            return "A wiki page with the slug '" . $slug . "' already exists.";
        }

        $page->setTitle($title);
        $page->setSlug($slug);
        $page->setBody($body);

        return true;
    }

    private function slugify($text)
    {
        $slug = strtolower(trim((string)$text));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ? $slug : 'page';
    }
}
