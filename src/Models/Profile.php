<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * Class Profile
 *
 * @package Phlexus\Modules\BaseUser\Models
 */
class Profile extends Model
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
        $this->hasMany('id', User::class, 'profileId', [
            'alias'      => 'users',
            'foreignKey' => [
                'message' => 'Profile is being used on Users',
            ],
        ]);

        $this->hasMany('id', Permission::class, 'profileId', [
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
        $user = User::getUser();

        if($user === null || !$user->profileId) {
            return new self;
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