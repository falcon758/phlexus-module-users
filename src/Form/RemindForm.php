<?php

/**
 * This file is part of the Phlexus CMS.
 *
 * (c) Phlexus CMS <cms@phlexus.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Form;

use Phlexus\Forms\CaptchaForm;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;

class RemindForm extends CaptchaForm
{
    /**
     * Initialize form
     */
    public function initialize()
    {   
        $emailField = $this->translation->setTypeForm()
                                          ->_('field-email-address');     

        $email = new Email('email', [
            'required'    => true,
            'class'       => 'form-control',
            'placeholder' => $emailField
        ]);

        $emailMessage = $this->translation->setTypeMessage()
                                          ->_('field-email-required');
        
        $email->addValidator(new PresenceOf(['message' => $emailMessage]));
        
        $this->add($email);
    }
}
