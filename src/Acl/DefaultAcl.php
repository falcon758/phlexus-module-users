<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Acl;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Enum;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;
use Phlexus\Modules\BaseUser\Models\User;
use Phlexus\Modules\BaseUser\Models\Profile;

final class DefaultAcl extends Memory
{

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

    private function loadPermissions(Profile $profile): void {
        $this->addRole(new Role($profile->name));

        foreach ($profile->getPermission() as $permission) {
            $this->addComponent(new Component($permission->resource), $permission->action);
            $this->allow($profile->name, $permission->resource, $permission->action);
        }
    }
}