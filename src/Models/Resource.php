<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;

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
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $resource;

    /**
     * @var string
     */
    public $action;

    /**
     * @var int
     */
    public $active;
    

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