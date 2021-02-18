<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

use Phalcon\Mvc\Controller;

/**
 * Abstract User Controller
 *
 * @package Phlexus\Modules\User\Controllers
 */
abstract class AbstractController extends Controller
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->tag->appendTitle(' - Phlexus User');
    }

    protected function create(): bool {
    }

    protected function update(): bool {
    }

    protected function delete(): bool {
    }
}
