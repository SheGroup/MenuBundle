<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\Service;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class MenuBuilder
{
    private ?Request $currentRequest = null;
    private ?string $currentRoute = null;
    private bool $currentPathIsSelected;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->currentPathIsSelected = false;
    }

    public function build(array $menu, array $parameters = []): array
    {
        $this->currentPathIsSelected = false;
        $required = ['attr', 'class', 'items', 'use_span'];
        foreach ($required as $r) {
            if (!isset($menu[$r])) {
                $menu[$r] = false;
            }
        }

        $arrays = ['attr', 'items'];
        foreach ($arrays as $a) {
            if (!is_array($menu[$a])) {
                $menu[$a] = [];
            }
        }

        foreach ($menu['items'] as $i => $j) {
            $menu['items'][$i] = $this->prepareItem($j, $parameters);
            if (count($menu['items'][$i]['items'])) {
                foreach ($menu['items'][$i]['items'] as $x => $y) {
                    $menu['items'][$i]['items'][$x] = $this->prepareItem($y, $parameters);
                    if ($menu['items'][$i]['items'][$x]['selected']) {
                        $menu['items'][$i]['selected'] = true;
                    }
                }
            }
        }
        $this->removeNotAllowed($menu['items']);
        $this->addClasses($menu['items']);

        $menu['attributes'] = $this->buildAttributes($menu['attr']);

        return $menu;
    }

    private function prepareItem(array $item, array $parameters = []): array
    {
        $required = [
            'active',
            'active_routes',
            'active_parameters',
            'anchor_attr',
            'anchor_class',
            'attr',
            'class',
            'credentials',
            'icon',
            'items',
            'name',
            'route',
            'route_parameters',
        ];
        foreach ($required as $r) {
            if (!array_key_exists($r, $item)) {
                $item[$r] = false;
            }
        }

        $arrays = ['active', 'active_routes', 'active_parameters', 'anchor_attr', 'attr', 'items', 'route_parameters'];
        foreach ($arrays as $a) {
            if (!is_array($item[$a])) {
                $item[$a] = [];
            }
        }

        $item['active_routes'] = array_merge($item['active'], $item['active_routes']);
        unset($item['active']);

        $parameters = array_merge($item['route_parameters'], $parameters);

        foreach ($item['items'] as $key => $value) {
            $item['items'][$key] = $this->prepareItem($value, $parameters);
            if ($item['items'][$key]['selected']) {
                $item['selected'] = true;
            }
        }

        $item['link'] = '#';
        if ($item['route']) {
            $item['link'] = $this->router->generate($item['route'], $parameters);
        }

        $item['attributes'] = $this->buildAttributes($item['attr']);
        $item['anchor_attributes'] = $this->buildAttributes($item['anchor_attr']);

        $this->removeNotAllowed($item['items']);

        if (array_key_exists('selected', $item) && $item['selected']) {
            $this->addClasses($item['items']);

            return $item;
        }

        $item['selected'] = $this->isSelected($item);
        $this->addClasses($item['items']);

        return $item;
    }

    private function addClasses(array &$items): void
    {
        $index = 0;
        $lastIndex = count($items) - 1;
        foreach ($items as $key => $item) {
            if (!$item['class']) {
                $items[$key]['class'] = '';
            }
            if (0 === $index) {
                $items[$key]['class'] .= ' first';
            }
            if ($lastIndex === $index) {
                $items[$key]['class'] .= ' last';
            }
            if ($item['selected']) {
                $items[$key]['class'] .= ' active';
            }
            ++$index;
        }
    }

    private function removeNotAllowed(array &$items): void
    {
        foreach ($items as $key => $item) {
            if (!$this->isAllowed($item)) {
                unset($items[$key]);
            }
        }
        foreach ($items as $key => $item) {
            if ('#' === $item['link'] && !$item['items']) {
                unset($items[$key]);
            }
        }
    }

    private function isAllowed(array $item): bool
    {
        $isAllowed = empty($item['credentials']) || $this->authorizationChecker->isGranted($item['credentials']);
        if (
            !empty($item['voter'])
            && !$this->authorizationChecker->isGranted($item['voter'], $item['object'] ?? null)
        ) {
            $isAllowed = false;
        }

        return $isAllowed;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function isSelected(array $item): bool
    {
        if ($this->currentPathIsSelected) {
            return false;
        }

        $request = $this->getCurrentRequest();
        $route = $this->getCurrentRoute();
        if (!$request || !$route) {
            return false;
        }

        if ($route === $item['route']) {
            if (!count($item['active_parameters'])) {
                return $this->currentPathIsSelected = true;
            }
            foreach ($item['active_parameters'] as $key => $value) {
                if ($request->get($key) === $value) {
                    return $this->currentPathIsSelected = true;
                }
            }
        }

        $matcher = function ($regex) use ($route) {
            return 1 === preg_match(sprintf('#%s#', $regex), $route);
        };

        foreach ($item['active_routes'] as $active) {
            if ($active instanceof Closure) {
                if ($active->__invoke($matcher, $route)) {
                    return $this->currentPathIsSelected = true;
                }
                continue;
            }
            if ($matcher->__invoke($active)) {
                if (!count($item['active_parameters'])) {
                    return $this->currentPathIsSelected = true;
                }
                foreach ($item['active_parameters'] as $key => $value) {
                    if ($request->get($key) === $value) {
                        return $this->currentPathIsSelected = true;
                    }
                }
            }
        }

        return false;
    }

    private function getCurrentRequest(): ?Request
    {
        if (!$this->currentRequest) {
            $this->currentRequest = $this->requestStack->getMainRequest();
        }

        return $this->currentRequest;
    }

    private function getCurrentRoute(): ?string
    {
        if (!$this->currentRoute) {
            $request = $this->getCurrentRequest();
            $this->currentRoute = $request ? $request->get('_route') : null;
        }

        return $this->currentRoute;
    }

    private function buildAttributes(array $attributes): string
    {
        $ret = '';
        foreach ($attributes as $name => $value) {
            $ret .= $name.'="'.$value.'" ';
        }

        return $ret;
    }
}
