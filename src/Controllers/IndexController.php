<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

/**
 * Class IndexController
 *
 * @package Phlexus\Modules\User\Controllers
 */
final class IndexController extends AbstractController
{
    /**
     * @return void
     */
    public function indexAction(): void
    {
        $this->tag->setTitle('Dashboard');
    }
}
