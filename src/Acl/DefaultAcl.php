<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Acl;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Enum;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;
use Phlexus\Modules\BaseUser\Models\Users;
use Phlexus\Modules\BaseUser\Models\Profiles;

final class DefaultAcl extends Memory
{

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultAction(Enum::DENY);

        $profile = Profiles::getUserProfile();

        if($profile === null) {
            $profile = Profiles::findFirstByname(Profiles::GUEST);
        }

        $this->loadPermissions($profile);
    }

    private function loadPermissions(Profiles $profile): void {
        $this->addRole(new Role($profile->name));

        foreach($profile->getPermissions() as $permission) {
            $this->addComponent(new Component($permission->resource), $permission->action);
            $this->allow($profile->name, $permission->resource, $permission->action);
        }
    }
}