<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phlexus\Libraries\Media\Models\Media;
use Phlexus\Models\Model;
use Phalcon\Di\Di;

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

    public const PASSWORD_REGEX = '/^((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[*?@#$%]).{8,40})$/';

    /**
     * @var User
     */
    protected static $user;

    /**
     * @var string
     */
    private $storePassword;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public string $email;

    /**
     * @var string
     */
    public string $password;

    /**
     * @var string|null
     */
    public $userHash;
    
    /**
     * @var string|null
     */
    public $hashCode;

    /**
     * @var int|null
     */
    public $attempts;

    /**
     * @var int
     */
    public int $profileID;

    /**
     * @var int|null
     */
    public $imageID;

    /**
     * @var int|null
     */
    public $removalRequested;

    /**
     * @var int|null
     */
    public $active;

    /**
     * @var string|null
     */
    public $lastLoginAt;

    /**
     * @var string|null
     */
    public $lastFailedLoginAt;

    /**
     * @var string|null
     */
    public $createdAt;

    /**
     * @var string|null
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
        
        $this->hasOne('imageID', Media::class, 'id', [
            'alias'    => 'profileImage',
            'reusable' => true,
        ]);
    }


    /**
     * Get encrypt fields
     *
     * @return array Fields
     */
    public static function getEncryptFields() : array
    {
        return [
            //'email'
        ];
    }

    /**
     * After Fetch
     *
     * @return void
     */
    public function afterFetch()
    {
        //parent::afterFetch();

        $this->storePassword = $this->password;
    }

    /**
     * Before Save
     *
     * @return void
     */
    public function beforeSave()
    {
        //parent::beforeSave();

        if (!isset($this->userHash)) {
            $this->userHash = $this->generateHash();
        }

        if (!isset($this->hashCode)) {
            $this->hashCode = $this->generateHash();
        }

        if ($this->password !== null && $this->storePassword !== $this->password) {
            $this->password = Di::getDefault()->getShared('security')->hash($this->password);
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
    public function activateUser(): bool
    {
        $this->active    = User::ENABLED;
        $this->hashCode = null;

        return $this->save();
    }

    /**
     * Change user password
     * 
     * @param string $password User password to set
     * 
     * @return bool
     */
    public function changePassword(string $password): bool
    {
        $this->password  = $password;
        $this->hashCode = null;

        return $this->save();
    }

    /**
     * Generate user HashCode
     * 
     * @return bool
     */
    public function generateHashCode(): bool
    {
        $this->hashCode = $this->generateHash();

        return $this->save();
    }

    /**
     * Generate Hash
     * 
     * @return bool
     */
    public function generateHash(): string
    {
        return Di::getDefault()->getShared('security')->getRandom()->base64Safe(self::HASHLENGTH);
    }

    /**
     * Get user info
     * 
     * @return object
     */
    public function getUserInfo(): array
    {
        if (!$this->id) {
            return [];
        }

        $media = $this->profileImage;

        $userDirectory = Di::getDefault()->getShared('security')->getStaticUserToken();

        return [
            'userDir'  => $userDirectory,
            'email'    => $this->email,
            'userType' => $this->profile->name,
            'image'    => $media ? $this->profileImage->mediaName : '',
        ];
    }

    /**
     * Find User by Email
     *
     * @param string $email User email
     *
     * @return mixed User Model or null
     */
    public static function findUserByEmail(string $email): ?User
    {
        // @ToDo: Encrypt emails
        return self::findFirstByEmail(/*self::encrypt(*/$email/*)*/);
    }

    /**
     * Create User
     * 
     * @param string $email    User email
     * @param string $password User password
     *
     * @return mixed User Model or null
     */
    public static function createUser(string $email, string $password): ?User
    {
        $newUser            = new self;
        $newUser->email     = $email;
        $newUser->password  = $password;
        $newUser->active    = User::DISABLED;
        $newUser->profileID = Profile::MEMBERID;

        if (!$newUser->save()) {
            return null;
        }

        return $newUser;
    }

    /**
     * Get Current User
     *
     * @param bool $forceGetUser
     * 
     * @return User|null
     */
    public static function getUser(bool $forceGetUser = false): ?User
    {
        $auth = DI::getDefault()->getShared('auth');

        $userID = (int) $auth->getIDentity();

        if ($userID === 0) {
            return null;
        }

        if (isset(self::$user) && !$forceGetUser) {
            return self::$user;
        }

        self::$user = static::findFirstByid($userID);

        return self::$user;
    }

    /**
     * Get user to activate
     * 
     * @param string $hashCode Hash code to search
     * 
     * @return mixed User or null
     */
    public static function getActivateUser(string $hashCode): ?User
    {
        return self::findFirst([
            'conditions' => 'active = :active: AND hashCode = :hashCode:',
            'bind'       => [
                'active'  => self::DISABLED,
                'hashCode'  => $hashCode
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
    public static function canLogin(string $email): bool
    {
        $user = self::findUserByEmail($email);

        if (!$user || $user->active === 0 || $user->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        return true;
    }
}
