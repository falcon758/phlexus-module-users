<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Events\Listeners;

use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Mvc\DispatcherInterface;
use Phlexus\Libraries\Auth\AuthException;
use Phlexus\Modules\BaseUser\Acl\Acl;

final class AuthorizationListener extends Injectable
{
    /**
     * This action is executed before execute any action in the application.
     *
     * @param Event $event Event object.
     * @param DispatcherInterface $dispatcher Dispatcher object.
     * @param array $data The event data.
     *
     * @return bool
     */
    public function beforeDispatchLoop(Event $event, DispatcherInterface $dispatcher, $data = null)
    {
        $di = $dispatcher->getDi();
        $acl = new Acl($di->get('acl'), $dispatcher, $di->get('view'));

        if(!$acl->isAllowed()) {
            $this->getDI()->getShared('eventsManager')->fire(
                'dispatch:beforeException',
                $dispatcher,
                new AuthException('User is not authorized.')
            );

            $event->stop();

            return $event->isStopped();
        }
    }
}
