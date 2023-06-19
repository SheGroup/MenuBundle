<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle;

use SheGroup\MenuBundle\DependencyInjection\CompilerPass\MenuLocatorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SheGroupMenuBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MenuLocatorCompilerPass());
    }
}
