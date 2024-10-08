<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phlexus\Models\Model;
use Phalcon\Di\Di;

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
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @var int
     */
    public int $resourceID;

    /**
     * @var int
     */
    public int $profileID;

    /**
     * @var string|null
     */
    public ?string $createdAt = null;

    /**
     * @var string|null
     */
    public ?string $modifiedAt = null;

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
