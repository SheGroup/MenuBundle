<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\Service;

use SheGroup\MenuBundle\Exception\InvalidMenuException;
use SheGroup\MenuBundle\Menu\MenuInterface;
use SheGroup\MenuBundle\Menu\RequestAwareInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class MenuLocator
{
    /** @var RequestStack */
    private $requestStack;

    /** @var array<string, MenuInterface> */
    private $menus;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->menus = [];
    }

    /**
     * @noinspection PhpUnused
     *
     * Used in CompilerPass.
     */
    public function addMenu(string $name, MenuInterface $menu): void
    {
        $this->menus[$name] = $menu;
    }

    /** @throws InvalidMenuException */
    public function locate(string $name): MenuInterface
    {
        try {
            return $this->loadRegistered($name);
        } catch (InvalidMenuException $exception) {
            if (!class_exists($name)) {
                throw new InvalidMenuException($name);
            }
            $menu = new $name();
            if (!$menu instanceof MenuInterface) {
                throw new InvalidMenuException($name);
            }

            return $menu;
        }
    }

    /** @throws InvalidMenuException */
    private function loadRegistered(string $name): MenuInterface
    {
        if (!isset($this->menus[$name])) {
            throw new InvalidMenuException($name);
        }
        $menu = $this->menus[$name];
        $this->injectRequest($menu);

        return $this->menus[$name];
    }

    private function injectRequest(MenuInterface $menu): void
    {
        $request = $this->requestStack->getMasterRequest();
        if ($menu instanceof RequestAwareInterface && $request) {
            $menu->setRequest($request);
        }
    }
}
