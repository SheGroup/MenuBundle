<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\Menu;

use Symfony\Component\HttpFoundation\Request;

interface RequestAwareInterface
{
    public function setRequest(Request $request): void;
}
