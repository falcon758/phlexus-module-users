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
use Phlexus\Modules\BaseUser\Models\User;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Check;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Confirmation;
use Phalcon\Filter\Validation\Validator\Regex;
use Phalcon\Filter\Validation\Validator\Email as EmailValidator;

class RegisterForm extends CaptchaForm
{
    /**
     * Initialize form
     */
    public function initialize()
    {        
        $translationForm = $this->translation->setPage()->setTypeForm();   

        $email = new Email('email', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-email-address')
        ]);
        
        $password = new Password('password', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-password')
        ]);

        $repeatPassword = new Password('repeat_password', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-repeat-password')
        ]);

        $acceptTerms = new Check('accept_terms', [
            'value'       => '1',
            'placeholder' => $translationForm->_('field-accept-terms')
        ]);

        $translationMessage = $this->translation->setTypeMessage();

        $email->addValidators([
            new PresenceOf(['message' => $translationMessage->_('field-email-required')]),
            new EmailValidator(['message' => $translationMessage->_('field-email-is-invalid')])
        ]);

        $password->addValidators([
            new PresenceOf(['message' => $translationMessage->_('field-password-required')]),
            new Regex(
                [
                    'pattern' => User::PASSWORD_REGEX,
                    'message' => $translationMessage->_('weak-password'),
                ]
            )
        ]);

        $repeatPassword->addValidator(
            new Confirmation([
                'with' => ['repeat_password' => 'password'],
                'message' => ['repeat_password' => $translationMessage->_('passwords-not-equal')]
            ])
        );

        $acceptTerms->addValidator(
            new PresenceOf([
                'message' => $translationMessage->_('terms-not-accepted')
            ])
        );

        $this->add($email);
        $this->add($password);
        $this->add($repeatPassword);
        $this->add($acceptTerms);
    }
}
