<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\Twig\Extension;

use SheGroup\MenuBundle\Exception\InvalidMenuException;
use SheGroup\MenuBundle\Service\MenuBuilder;
use SheGroup\MenuBundle\Service\MenuLocator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class MenuExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private MenuLocator $menuLocator, private MenuBuilder $menuBuilder)
    {
    }

    /** @throws LoaderError|SyntaxError|RuntimeError|InvalidMenuException */
    public function render(Environment $env, string $name, string $template = 'sidebar', array $parameters = []): string
    {
        $builder = $this->menuLocator->locate($name);
        $menu = $builder->getMenu();
        $menu = $this->menuBuilder->build($menu, $parameters);
        $loader = $env->getLoader();
        if (
            method_exists($loader, 'exists')
            && $loader->exists(sprintf('@SheGroupMenu/Menu/%s.html.twig', $template))
        ) {
            return $env->render(sprintf('@SheGroupMenu/Menu/%s.html.twig', $template), ['menu' => $menu]);
        }

        return $env->render($template, ['menu' => $menu]);
    }
}
