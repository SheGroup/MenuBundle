<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\Menu;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface MenuInterface
{
    public function getMenu(array $parameters = []): array;

    public function configureParameters(OptionsResolver $resolver): void;
}
