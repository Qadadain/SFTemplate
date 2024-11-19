<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsController]
#[Route('/', name: self::ROUTE_NAME, methods: ['GET'])]
final readonly class HomeAction
{
    public const string ROUTE_NAME = 'app_home';

    public function __construct(
        private Environment $renderer,
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function __invoke(): Response
    {
        return new Response($this->renderer->render(name: 'home.html.twig'));
    }
}
