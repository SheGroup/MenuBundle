MenuBundle
=============

The `MenuBundle` means easy-to-implement and feature-rich menus in your Symfony application!

## Installation

### Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require shegroup/menu-bundle
```

This command requires you to have Composer installed globally, as explained
in the `installation chapter` of the Composer documentation.

### Enable the Bundle

Then, enable the bundle by adding the following line in the ``app/AppKernel.php``
file of your project:

```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new SheGroup\MenuBundle\SheGroupMenuBundle(),
        );

        // ...
    }

    // ...
}
```

## Create your first menu

An example builder class would look like this:

```php

<?php

declare(strict_types=1);

namespace AppBundle\Menu;

use SheGroup\MenuBundle\Menu\MenuInterface;

final class MainMenu implements MenuInterface
{
    public function getMenu(): array
    {
        $contact = ...

        return [
            'class' => 'sidebar-menu',
            'items' => [
                [
                    'name' => 'Users',
                    'icon' => 'fa fa-user',
                    'items' => [
                        [
                            'name' => 'Admins',
                            'route' => 'admin_core_user_admin_list',
                            'active_routes' => [
                                'admin_core_user_admin_[\w]+',
                            ],
                        ],
                        [
                            'name' => 'Clients',
                            'route' => 'admin_core_user_client_list',
                            'active_routes' => [
                                'admin_core_user_client_[\w]+',
                                static function (Closure $matcher) use ($contact) {
                                    if (!$matcher->__invoke('_app.contact[\w\_\.]+')) {
                                        return false;
                                    }
                                    if ($contact instanceof Person) {
                                        return PersonType::isClientRelated($contact->getPersonType());
                                    }
            
                                    return $contact instanceof Client;
                                },
                            ],
                        ],
                    ],
                ],                
                [
                    'name' => 'Groups',
                    'route' => 'admin_core_group_list',
                    'icon' => 'fa fa-users',
                    'active_routes' => [
                        'admin_core_group_[\w]+',
                        '_admin.group.[\w\.]',
                    ],
                ],
            ],
        ];
    }
}
```

The menu needs to implement MenuInterface. If you need to access the request information,
your menu has to implement RequestAwareInterface as well.

## Render

To actually render the menu, just do the following from anywhere in any template:

```html+jinja
{{ renderMenu('AppBundle\\Menu\\MainMenu', 'sidebar') }}
```

You can define your menu as a service. In order to do that, you need to tag your service
with the 'she_group.menu' tag:

```yaml
app.main_menu:
  class: AppBundle\Menu\MainMenu
  tags: [ 'she_group.menu' ]
```

this tag is automatically added to all classes that implements MenuInterface when autoconfiguration
is enabled.

In order to render it:

```html+jinja
{{ renderMenu('app.main_menu', 'sidebar') }}
```
