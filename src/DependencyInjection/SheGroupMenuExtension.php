<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\DependencyInjection;

use Exception;
use SheGroup\MenuBundle\Menu\MenuInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SheGroupMenuExtension extends Extension
{
    /** @throws Exception */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        foreach ($config as $parameter => $name) {
            $container->setParameter('she_group_menu.'.$parameter, $name);
        }

        $servicesDirectory = __DIR__.'/../Resources/config/services';
        $finder = new Finder();
        $loader = new YamlFileLoader($container, new FileLocator($servicesDirectory));
        $finder->in($servicesDirectory);
        $files = $finder->name('*.yaml')->files();
        foreach ($files as $file) {
            $loader->load($file->getFilename());
        }
        $container->registerForAutoconfiguration(MenuInterface::class)->addTag('she_group.menu');
    }
}
