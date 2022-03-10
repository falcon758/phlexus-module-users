<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Acl;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Enum;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;
use Phlexus\Modules\BaseUser\Models\User;
use Phlexus\Modules\BaseUser\Models\Profile;

/**
 * DefaultAcl
 *
 * @package Phlexus\Modules\BaseUser\Acl
 */
final class DefaultAcl extends Memory
{

    private Profile $profile;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultAction(Enum::DENY);

        $profile = Profile::getUserProfile();

        if (!$profile->id) {
            $profile = Profile::findFirstByid(Profile::GUESTID);
        }

        $this->profile = $profile;

        $this->loadPermissions($profile);
    }

    /**
     * Check if has permissions
     * 
     * @param string $module     Module
     * @param string $controller Controller
     * @param string $action     Action
     * 
     * @return bool
     */
    public function hasPermission(string $module, string $controller, string $action): bool {
        $component = strtolower($module . '_' . $controller);

        if ($this->isComponent($component)) {
            if ($this->isAllowed($this->profile->name, $component, strtolower($action))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Load user permissions
     * 
     * @param Profile $profile User profile to load
     * 
     * @return void
     */
    private function loadPermissions(Profile $profile): void {
        $this->addRole(new Role($profile->name));

        foreach ($profile->getProfileResource() as $profileResource) {
            $resource = $profileResource->resource;
            $this->addComponent(new Component($resource->resource), $resource->action);
            $this->allow($profile->name, $resource->resource, $resource->action);
        }
    }
}