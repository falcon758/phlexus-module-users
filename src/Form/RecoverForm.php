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
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\Alnum;

class RecoverForm extends CaptchaForm
{
    /**
     * Initialize form
     */
    public function initialize()
    {
        $translationForm = $this->translation->setTypeForm();   

        $hashCode = new Hidden('hash_code', [
            'required' => true,
            'class'    => 'form-control'
        ]);

        $password = new Password('password', [
            'required'    => true,
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-password')
        ]);

        $repeatPassword = new Password('repeat_password', [
            'required'    => true,
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-repeat-password')
        ]);

        $translationMessage = $this->translation->setTypeMessage();
        
        $hashCode->addValidator(new Alnum(
            [
                'message' => $translationMessage->_('hash-code-must-have-only-alphanumeric-characters')
            ]
        ));

        $password->addValidator(new PresenceOf(['message' => $translationMessage->_('field-password-required')]));
        
        $repeatPassword->addValidator(
            new Identical([
                'value'   => $password->getValue(),
                'message' => $translationMessage->_('passwords-not-equal')
            ])
        );

        $this->add($hashCode);
        $this->add($password);
        $this->add($repeatPassword);
    }
}
