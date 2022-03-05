<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;
use Phalcon\DI;

/**
 * Class User
 *
 * @package Phlexus\Modules\BaseUser\Models
 * 
 * @ToDo: Only fetch enabled users, if status not specified
 */
class User extends Model
{
    private const HASHLENGTH = 40;

    private const MAX_ATTEMPTS = 5;

    public const DISABLED = 0;

    public const ENABLED = 1;

    private $storePassword;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $user_hash;
    
    /**
     * @var string
     */
    public $hash_code;

    /**
     * @var int
     */
    public $profileID;

    /**
     * @var int
     */
    public $imageID;

    /**
     * @var int
     */
    public $attempts;

    /**
     * @var int
     */
    public $active;

    /**
     * @var string
     */
    public $lastLoginAt;

    /**
     * @var string
     */
    public $lastFailedLoginAt;

    /**
     * @var string
     */
    public $createdAt;

    /**
     * @var string
     */
    public $modifiedAt;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('users');

        $this->hasOne('profileID', Profile::class, 'id', [
            'alias'    => 'profile',
            'reusable' => true,
        ]);
    }

    /**
     * After Fetch
     *
     * @return void
     */
    public function afterFetch()
    {
        $this->storePassword = $this->password;
    }

    /**
     * Before Save
     *
     * @return void
     */
    public function beforeSave()
    {
        if ($this->password !== null && $this->storePassword !== $this->password) {
            $this->password = Di::getDefault()->getShared('security')->hash($this->password);
        }
    }

    /**
     * Create User
     * 
     * @param string $email    User email
     * @param string $password User password
     *
     * @return mixed User Model or null
     */
    public function createUser(string $email, string $password)
    {
        $security = Di::getDefault()->getShared('security');

        $this->email     = $email;
        $this->password  = $password;
        $this->active    = User::DISABLED;
        $this->profileID = Profile::MEMBERID;
        $this->user_hash = $this->generateHash();
        $this->hash_code = $this->generateHash();

        if (!$this->save()) {
            return null;
        }

        return $this;
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
        if (!$this->id) { return false; }

        $ts = date('Y-m-d H:i:s', time());

        $this->lastFailedLoginAt = $ts;
        $this->attempts = intval($this->attempts) + 1;
        
        return $this->save();
    }

    /**
     * Activate User
     * 
     * @return bool
     */
    public function activateUser(): bool {
        $this->active    = User::ENABLED;
        $this->hash_code = null;

        return $this->save();
    }

    /**
     * Change user password
     * 
     * @param string $password User password to set
     * 
     * @return bool
     */
    public function changePassword(string $password): bool {
        $this->password  = $password;
        $this->hash_code = null;

        return $this->save();
    }

    /**
     * Set user HashCode
     *
     * @param string $hashCode User HashCode
     * 
     * @return bool
     */
    public function setHashCode(string $hashCode): bool {
        $this->hash_code = $hashCode;

        return $this->save();
    }


    /**
     * Generate user Hash
     *
     * @param string $hashCode User HashCode
     * 
     * @return void
     */
    public function generateUserHash(): void {
        $this->user_hash = $this->generateHash();
    }

    /**
     * Generate user HashCode
     * 
     * @return bool
     */
    public function generateHashCode(): bool {
        $this->hash_code = $this->generateHash();

        return $this->save();
    }

    /**
     * Generate Hash
     * 
     * @return bool
     */
    public function generateHash(): string {
        return Di::getDefault()->getShared('security')->getRandom()->base64Safe(self::HASHLENGTH);
    }

    /**
     * Get user info
     * 
     * @return object
     */
    public function getUserInfo(): array {
        return [
            'email'    => $this->email,
            'userType' => $this->profile->name,
        ];
    }

    /**
     * Get Current User
     *
     * @return User
     */
    public static function getUser(): User {
        $auth = DI::getDefault()->getShared('auth');

        $userID = (int) $auth->getIDentity();

        if ($userID === 0) {
            return new self;
        }

        return self::findFirstByid($userID);
    }

    /**
     * Get user to activate
     * 
     * @param string $hashCode Hash code to search
     * 
     * @return User
     */
    public static function getActivateUser(string $hashCode): User {
        return self::findFirst([
            'conditions' => "active = :active: AND hash_code = :hash_code:",
            'bind'       => [
                'active'  => self::DISABLED,
                'hash_code'  => $hashCode
            ],
        ]);
    }

    /**
     * Can User Login
     *
     * @param string $email User email to validate
     * 
     * @return bool
     */
    public static function canLogin(string $email): bool {
        $user = self::findFirstByEmail($email);

        if (!$user || $user->active === 0 || $user->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        return true;
    }
}
