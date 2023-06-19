<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\DependencyInjection\CompilerPass;

use SheGroup\MenuBundle\Service\MenuLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MenuLocatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(MenuLocator::class)) {
            return;
        }

        $menus = array_keys($container->findTaggedServiceIds('she_group.menu'));
        $locator = $container->getDefinition(MenuLocator::class);
        foreach ($menus as $menuId) {
            $reference = new Reference($menuId);
            $locator->addMethodCall('addMenu', [$menuId, $reference]);
            $menuDefinition = $container->getDefinition($menuId);
            $locator->addMethodCall('addMenu', [$menuDefinition->getClass(), $reference]);
        }
    }
}
