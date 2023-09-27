<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MenuExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
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
