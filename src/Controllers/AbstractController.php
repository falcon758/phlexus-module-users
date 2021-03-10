<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

use Phalcon\Mvc\Controller;

/**
 * Abstract User Controller
 *
 * @package Phlexus\Modules\BaseUser\Controllers
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

    /**
     * Get Base Position
     *
     * @return string Current base position (module/controller)
     */
    public function getBasePosition(): string
    {
        $module = $this->dispatcher->getModuleName();
        $controller = $this->dispatcher->getControllerName();

        return strtolower($module) . '/' . strtolower($controller);
    }
}
