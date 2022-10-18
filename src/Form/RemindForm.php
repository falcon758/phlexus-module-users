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
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Email as EmailValidator;

class RemindForm extends CaptchaForm
{
    /**
     * Initialize form
     */
    public function initialize()
    {   
        $emailField = $this->translation->setPage()->setTypeForm()
                                          ->_('field-email-address');     

        $email = new Email('email', [
            'class'       => 'form-control',
            'placeholder' => $emailField
        ]);

        $translationMessage = $this->translation->setTypeMessage();
        
        $email->addValidators([
            new PresenceOf(['message' => $translationMessage->_('field-email-required')]),
            new EmailValidator(['message' => $translationMessage->_('field-email-is-invalid')])
        ]);
        
        $this->add($email);
    }
}
