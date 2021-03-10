<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;

/**
 * Class Users
 *
 * @package Phlexus\Modules\BaseUser\Models
 */
class Users extends Model
{
    public $id;

    public $email;

    public $password;

    public $profileId;

    public $active;

    public function initialize()
    {
        $this->hasOne('profileId', Profiles::class, 'id', [
            'alias'    => 'profile',
            'reusable' => true,
        ]);
    }

    public static function getUser() {
        $auth = DI::getDefault()->getShared('auth');

        $userId = (int) $auth->getIdentity();

        if($userId === 0) {
            return null;
        }

        return self::findFirstByid($userId);
    }
}
