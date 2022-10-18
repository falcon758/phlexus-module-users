<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

use Phalcon\Tag;

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

        Tag::setTitle($title);
    }
}
