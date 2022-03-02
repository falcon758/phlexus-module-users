<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;
use Phalcon\Di;

/**
 * Class ProfileResource
 *
 * @package Phlexus\Modules\BaseUser\Models
 */
class ProfileResource extends Model
{
    public const DISABLED = 0;

    public const ENABLED = 1;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $resourceID;

    /**
     * @var int
     */
    public $profileID;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('profile_resources');

        $this->hasOne('resourceID', Resource::class, 'id', [
            'alias'    => 'resource',
            'reusable' => true,
        ]);

        $this->hasOne('profileID', Profile::class, 'id', [
            'alias'    => 'profile',
            'reusable' => true,
        ]);
    }
}
