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

class LoginForm extends CaptchaForm
{
    /**
     * Initialize form
     */
    public function initialize()
    {   
        $translationForm = $this->translation->setPage()->setTypeForm();   

        $email = new Email('email', [
            'required'    => true,
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-email-address')
        ]);
        
        $password = new Password('password', [
            'required'    => true,
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-password')
        ]);

        $translationMessage = $this->translation->setTypeMessage();   

        $email->addValidator(new PresenceOf(['message' => $translationMessage->_('field-email-required')]));
        
        $password->addValidator(new PresenceOf(['message' => $translationMessage->_('field-password-required')]));
        
        $this->add($email);
        $this->add($password);
    }
}
