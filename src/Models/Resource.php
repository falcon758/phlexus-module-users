<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phlexus\Models\Model;

/**
 * Class Resource
 *
 * @package Phlexus\Modules\BaseUser\Models
 */
class Resource extends Model
{
    public const DISABLED = 0;

    public const ENABLED = 1;

    /**
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @var string
     */
    public string $resource;

    /**
     * @var string
     */
    public string $action;

    /**
     * @var int|null
     */
    public ?int $active = null;

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
        $this->setSource('resources');

        $this->hasMany('id', ProfileResource::class, 'resourceID', [
            'alias'      => 'profileResource',
            'foreignKey' => [
                'message' => 'Profile is being used on ProfileResource',
            ],
        ]);
    }
}