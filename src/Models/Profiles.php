<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * Class Profiles
 *
 * @package Phlexus\Modules\BaseUser\Models
 */
class Profiles extends Model
{
    public const ADMIN = 'admin';

    public const MEMBER = 'member';

    public const GUEST = 'guest';

    public $id;

    public $name;

    public $active;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->hasMany('id', Users::class, 'profileId', [
            'alias'      => 'users',
            'foreignKey' => [
                'message' => 'Profile is being used on Users',
            ],
        ]);

        $this->hasMany('id', Permissions::class, 'profileId', [
            'alias'      => 'permissions',
            'foreignKey' => [
                'action' => Relation::ACTION_CASCADE,
            ],
        ]);
    }

    /**
     * Get active profiles
     *
     * @return Simple
     */
    public static function getProfiles(): Simple {
        return self::find([
            'active = :active:',
            'bind' => [
                'active' => 1
            ]
        ]);
    }

    /**
     * Get user profile
     *
     * @return Profiles
     */
    public static function getUserProfile(): Profiles {
        $user = Users::getUser();

        if($user === null) {
            return null;
        }

        return self::findFirstByid($user->profileId);
    }

    /**
     * Is admin
     *
     * @return bool
     */
    public function isAdmin() {
        return $this->name === self::ADMIN;
    }
}