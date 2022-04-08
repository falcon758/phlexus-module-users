<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

/**
 * Class ErrorsController
 *
 * @package Phlexus\Modules\BaseUser\Controllers
 */
final class ErrorsController extends AbstractController
{
    /**
     * 402 Action
     *
     * @return void
     */
    public function show402Action(): void
    {
        $title = $this->translation->setTypePage()->_('title-error-402');

        $this->tag->setTitle($title);    }

    /**
     * 404 Action
     *
     * @return void
     */
    public function show404Action(): void
    {
        $title = $this->translation->setTypePage()->_('title-error-404');

        $this->tag->setTitle($title);
    }

    /**
     * 500 Action
     *
     * @return void
     */
    public function show500Action(): void
    {
        $title = $this->translation->setTypePage()->_('title-error-500');

        $this->tag->setTitle($title);
    }
}
