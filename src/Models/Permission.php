<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;

/**
 * Class Permission
 *
 * @package Phlexus\Modules\BaseUser\Models
 */
class Permission extends Model
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
    public $profileID;

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
        $this->setSource('permissions');

        $this->belongsTo('profileID', Profile::class, 'id', [
            'alias' => 'profile',
        ]);
    }
}