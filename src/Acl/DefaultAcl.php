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
            return;
        }

        $this->loadRoles($profile);

        $this->loadPermissions($profile);
    }

    private function loadRoles(Profiles $profile): void {
        $this->addRole(new Role($profile->type));
    }

    private function loadPermissions(Profiles $profile): void {
        $aclFile = ROOT_PATH . '/config/acl.php';

        $components = [];

        if(file_exists($aclFile)) {
            $components = require_once $aclFile;
        }

        $profileName = $profile->type;

        if(count($components) === 0 || !array_key_exists($profileName, $components)) {
            return;
        }
        
        $component = $components[$profileName];

        foreach($component as $comp => $action) {
            $this->addComponent(new Component($comp), $action);
            
            $this->allow($profileName, $comp, $access);
        }

        $this->allow('*', '*', '*');
    }
}