<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Events\Listeners;

use Exception;
use Phalcon\Application\Exception as ApplicationException;
use Phalcon\Di\Exception as DiException;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as MvcDispatcherException;
use Phlexus\Libraries\Auth\AuthException;
use Phlexus\Module\ModuleException;
use Phlexus\Module\ModuleInterface;
use Phlexus\Modules\BaseUser\Module as UserModule;
use Phlexus\Helpers;

final class DispatcherListener extends Injectable
{
    /**
     * Before forwarding is happening.
     *
     * @param Event $event Event object.
     * @param Dispatcher $dispatcher Dispatcher object.
     * @param array $forward The forward data.
     *
     * @return bool
     * @throws DiException
     * @throws Exception
     */
    public function beforeForward(Event $event, Dispatcher $dispatcher, array $forward = []): bool
    {
        if (!empty($forward['module'])) {
            $moduleName = $forward['module'];
            if (!Helpers::phlexusContainer('modules')->offsetExists($moduleName)) {
                throw new ModuleException("Module {$moduleName} does not exist.");
            }

            $moduleDefinition = Helpers::phlexusContainer('modules')->offsetGet($moduleName);
            // TODO: think about that validation, as there are atm only vendor DI declaration
            /*if (!Helpers::phlexusContainer()->has($moduleDefinition->className)) {
                throw new DiException(
                    "Service '{$moduleDefinition->className}' wasn't found in the dependency injection container"
                );
            }*/

            /** @var ModuleInterface $module */
            $moduleClass = Helpers::phlexusContainer($moduleDefinition->className);
            $dispatcher->setModuleName($moduleName);
            $dispatcher->setNamespaceName($moduleClass->getHandlersNamespace());
        }

        return $event->isStopped();
    }

    /**
     * Before exception is happening.
     *
     * @param Event $event Event object.
     * @param Dispatcher $dispatcher Dispatcher object.
     * @param Exception $exception Exception object.
     *
     * @throws Exception
     * @return bool
     */
    public function beforeException(Event $event, Dispatcher $dispatcher, Exception $exception): bool
    {
        $moduleName = UserModule::getModuleName();
        $namespace = UserModule::getHandlersNamespace() . '\\Controllers';

        if ($exception instanceof AuthException) {
            $this->response->setStatusCode(402);
            $dispatcher->forward([
                'module'     => $moduleName,
                'namespace'  => $namespace,
                'controller' => 'auth',
                'action'     => 'login',
            ]);

            $event->stop();
        }

        return $event->isStopped();
    }
}
