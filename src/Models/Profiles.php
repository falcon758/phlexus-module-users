<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Relation;

class Profiles extends Model
{
    public const ADMIN = 'admin';

    public const MEMBER = 'member';

    public const GUEST = 'guest';

    public $id;

    public $name;

    public $active;

    public function initialize()
    {
        $this->hasMany('id', Users::class, 'profilesId', [
            'alias'      => 'users',
            'foreignKey' => [
                'message' => 'Profile is being used on Users',
            ],
        ]);

        $this->hasMany('id', Permissions::class, 'profilesId', [
            'alias'      => 'permissions',
            'foreignKey' => [
                'action' => Relation::ACTION_CASCADE,
            ],
        ]);
    }

    public static function getProfile() {
        $user = Users::getUser();

        if($user === null) {
            return null;
        }

        return self::findFirstByid($user->profileId);
    }

    public static function getProfiles() {
        return Profiles::find([
            'active = :active:',
            'bind' => [
                'active' => 1
            ]
        ]);
    }
}