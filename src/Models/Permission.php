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
    const DISABLED = 0;

    const ENABLED = 1;

    public $id;

    public $profileId;

    public $resource;

    public $action;

    public $active;
    

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('permissions');

        $this->belongsTo('profileId', Profile::class, 'id', [
            'alias' => 'profile',
        ]);
    }
}