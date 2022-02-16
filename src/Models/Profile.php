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

    public const DISABLED = 0;

    public const ENABLED = 1;

    public const ADMINID = 1;

    public const MEMBERID = 2;

    public const GUESTID = 3;

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
        $this->setSource('profiles');

        $this->hasMany('id', User::class, 'profileID', [
            'alias'      => 'user',
            'foreignKey' => [
                'message' => 'Profile is being used on User',
            ],
        ]);

        $this->hasMany('id', Permission::class, 'profileID', [
            'alias'      => 'permission',
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
     * @return Profile
     */
    public static function getUserProfile(): Profile {
        $user = User::getUser();

        if ($user === null || !$user->profileID) {
            return new self;
        }

        return self::findFirstByid($user->profileID);
    }

    /**
     * Is admin
     *
     * @return bool
     */
    public function isAdmin() {
        return ((int) $this->id) === self::ADMINID;
    }
}