<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 12/9/2024
 * Time: 11:34 AM
 */

namespace App\UserdirectoryBundle\Security\Authentication;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface {

    public function __construct( private UrlGeneratorInterface $urlGenerator ) {

    }

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        exit('AuthenticationEntryPoint: start');

        // add a custom flash message and redirect to the login page
        $request->getSession()->getFlashBag()->add('note', 'You have to login in order to access this page.');

        //dump($request);
        //exit('111');

        return new RedirectResponse($this->urlGenerator->generate('employees_login'));
    }

}