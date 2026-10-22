<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $isAdmin = in_array('ROLE_ADMIN', $token->getRoleNames(), true);
        $adminLogin = $request->attributes->get('_route') === 'app_admin_login';

        if ($isAdmin) {
            if (!$adminLogin) {
                return new RedirectResponse($this->urlGenerator->generate('app_home_back'));
            }

            $targetPath = $this->getTargetPath($request->getSession(), 'admin');

            return new RedirectResponse($targetPath ?? $this->urlGenerator->generate('app_home_back'));
        }

        if ($adminLogin) {
            $session = $request->getSession();
            if (!$session instanceof FlashBagAwareSessionInterface) {
                throw new \LogicException('La session ne prend pas en charge les messages flash.');
            }

            $session->getFlashBag()->add('danger', 'L’accès au back-office est réservé aux administrateurs.');
        }

        $targetPath = $this->getTargetPath($request->getSession(), 'main');

        return new RedirectResponse($targetPath ?? $this->urlGenerator->generate('app_home'));
    }
}
