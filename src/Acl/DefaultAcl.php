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

        $this->loadPermissions($profile);
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

        foreach ($profile->getPermission() as $permission) {
            $this->addComponent(new Component($permission->resource), $permission->action);
            $this->allow($profile->name, $permission->resource, $permission->action);
        }
    }
}