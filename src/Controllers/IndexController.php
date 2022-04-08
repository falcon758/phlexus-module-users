<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

/**
 * Class IndexController
 *
 * @package Phlexus\Modules\BaseUser\Controllers
 */
final class IndexController extends AbstractController
{
    /**
     * @return void
     */
    public function indexAction(): void
    {
        $title = $this->translation->setTypePage()->_('title-dashboard');

        $this->tag->setTitle($title);
    }
}
