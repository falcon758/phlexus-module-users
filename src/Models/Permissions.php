<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Models;

use Phalcon\Mvc\Model;

/**
 * Class Permissions
 *
 * @package Phlexus\Modules\BaseUser\Models
 */
class Permissions extends Model
{
    public $id;

    public $profileId;

    public $resource;

    public $action;
    
    public function initialize()
    {
        $this->belongsTo('profileId', Profiles::class, 'id', [
            'alias' => 'profile',
        ]);
    }
}