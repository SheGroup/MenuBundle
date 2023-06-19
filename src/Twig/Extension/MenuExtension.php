<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;

final class MenuExtension extends Twig_Extension
{
    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction(
                'renderMenu',
                [MenuExtensionRuntime::class, 'render'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
        ];
    }
}
