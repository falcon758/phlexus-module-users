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
    public $id;

    public $profileId;

    public $resource;

    public $action;
    

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->belongsTo('profileId', Profile::class, 'id', [
            'alias' => 'profile',
        ]);
    }
}