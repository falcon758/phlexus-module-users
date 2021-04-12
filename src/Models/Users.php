<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;
use Phalcon\Security;

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

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->hasOne('profileId', Profiles::class, 'id', [
            'alias'    => 'profile',
            'reusable' => true,
        ]);
    }

    /**
     * Before Save
     *
     * @return void
     */
    public function beforeSave()
    {
        if($this->password !== null) {
            $security = new Security();
            $this->password = $security->hash($this->password);
        }
    }

    /**
     * Get Current User
     *
     * @return Users
     */
    public static function getUser(): Users {
        $auth = DI::getDefault()->getShared('auth');

        $userId = (int) $auth->getIdentity();

        if($userId === 0) {
            return new self;
        }

        return self::findFirstByid($userId);
    }
}
