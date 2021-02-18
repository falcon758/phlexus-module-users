<?php

/**
 * This file is part of the Phlexus CMS.
 *
 * (c) Phlexus CMS <cms@phlexus.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phlexus\Modules\BaseUser;

use Phalcon\Di\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View\Engine\Volt;
use Phlexus\Module as PhlexusModule;
use Phlexus\Modules\BaseUser\Events\Listeners\AuthenticationListener;
use Phlexus\Modules\BaseUser\Events\Listeners\AuthorizationListener;
use Phlexus\Modules\BaseUser\Events\Listeners\DispatcherListener;
use Phlexus\Modules\BaseUser\Acl\DefaultAcl;

/**
 * User Module
 */
class Module extends PhlexusModule
{
    /**
     * Name of theme
     *
     * Which is also folder name inside themes folder.
     */
    const PHLEXUS_USER_THEME_NAME = 'phlexus-tabler-admin';

    /**
     * @return string
     */
    public static function getModuleName(): string
    {
        $namespaceParts = explode('\\', __NAMESPACE__);

        return end($namespaceParts);
    }

    /**
     * @return string
     */
    public static function getHandlersNamespace(): string
    {
        return __NAMESPACE__;
    }

    /**
     * Registers an autoloader related to the module.
     *
     * @param DiInterface $di
     * @return void
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        (new Loader())
            ->registerNamespaces([
                self::getHandlersNamespace() . '\\Models' => __DIR__ . '/Models/',
                self::getHandlersNamespace() . '\\Controllers' => __DIR__ . '/Controllers/',
                self::getHandlersNamespace() . '\\Events' => __DIR__ . '/Events/',
                self::getHandlersNamespace() . '\\Acl' => __DIR__ . '/Acl/',
            ])
            ->register();
    }

    /**
     * @param DiInterface|null $di
     * @return void
     */
    public function registerServices(DiInterface $di = null)
    {
        $view = $di->getShared('view');
        $theme = $di->getShared('config')->get('theme');

        $themePath = $theme->themes_dir . self::PHLEXUS_USER_THEME_NAME;
        $cacheDir = $theme->themes_dir_cache;

        $view->registerEngines([
            '.volt' => function ($view) use ($cacheDir, $di) {
                $volt = new Volt($view, $di);
                $volt->setOptions([
                    'path' => $cacheDir,
                ]);

                return $volt;
            }
        ]);

        $view->setMainView($themePath . '/layouts/default');
        $view->setViewsDir($themePath . '/');

        // Default Acl
        $acl = new DefaultAcl;
        $di->set('acl', $acl);

        $di->getShared('eventsManager')->attach('dispatch', new DispatcherListener());
        $di->getShared('eventsManager')->attach('dispatch:beforeDispatchLoop', new AuthenticationListener());
        $di->getShared('eventsManager')->attach('dispatch:beforeDispatchLoop', new AuthorizationListener());
    }
}
