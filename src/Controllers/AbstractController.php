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
     * Get Base Position
     *
     * @return string Current base position (module/controller)
     */
    public function getBasePosition(): string
    {
        $module = strtolower($this->dispatcher->getModuleName());
        $controller = strtolower($this->dispatcher->getControllerName());

        if ($module !== $controller) {
            $basePosition = $module . '/' . $controller;
        } else {
            $basePosition = $controller;
        }

        return '/' . $basePosition;
    }
}
