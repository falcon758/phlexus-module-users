<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Acl;

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Phlexus\Modules\BaseUser\Models\Profile;
use Phalcon\Mvc\Dispatcher\Exception as MvcDispatcherException;

/**
 * Acl
 *
 * @package Phlexus\Modules\BaseUser\Acl
 */
final class Acl
{
    private bool $isAllowed = false;

    /**
     * Construct
     */
    public function __construct(DefaultAcl $acl, Dispatcher $dispatcher, View $view)
    {
        $profile = Profile::getUserProfile();

        if (!$profile->id) {
            $profile = Profile::GUEST;
        } else {
            $profile = $profile->name;
        }

        $module = $dispatcher->getModuleName();
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        $component = strtolower($module . '_' . $controller);
        if ($acl->isComponent($component)) {
            if ($acl->isAllowed($profile, $component, strtolower($action))) {
                $this->isAllowed = true;
            }
        } else {
            throw new MvcDispatcherException('Resource not found.');
        }
    }

    /**
     * Is user allowed
     * 
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }
}