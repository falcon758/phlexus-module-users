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
    const MAX_ATTEMPTS = 5;

    public $id;

    public $email;

    public $password;

    public $active;

    public $attempts;

    public $lastLoginAt;

    public $lastFailedLoginAt;

    public $createdAt;

    public $modifiedAt;

    public $profileId;

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
     * Register successfull login
     *
     * @return bool
     */
    public function successfullLogin()
    {
        $ts = date('Y-m-d H:i:s', time());

        $this->lastLoginAt = $ts;
        $this->attempts = 0;
        
        return $this->save();
    }

    /**
     * Register failed login
     *
     * @return bool
     */
    public function failedLogin()
    {
        if(!$this->id) { return false; }

        $ts = date('Y-m-d H:i:s', time());

        $this->lastFailedLoginAt = $ts;
        $this->attempts = intval($this->attempts) + 1;
        
        return$this->save();
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

    /**
     * Can User Login
     *
     * @param string $email User email to validate
     * 
     * @return bool
     */
    // TODO: Verify if passwords attempts exceeded
    public static function canLogin(string $email): bool {
        $user = self::findFirstByEmail($email);

        if(!$user || $user->active === 0 || $user->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        return true;
    }
}
