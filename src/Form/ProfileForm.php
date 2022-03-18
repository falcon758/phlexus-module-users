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
use Phalcon\Forms\Element\File;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\File as FileValidator;

class ProfileForm extends CaptchaForm
{
    /**
     * Initialize form
     */
    public function initialize()
    {
        $translationForm = $this->translation->setTypeForm();   

        $email = new Email('email', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-email'),
            'readonly'    => true
        ]);
        
        $password = new Password('password', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-password'),
        ]);

        $repeatPassword = new Password('repeat_password', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-repepat-password'),
        ]);

        $profileImage = new File('profile_image', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-profile-image'),
        ]);
                
        $passwordValue = $password->getValue();

        $translationMessage = $this->translation->setTypeMessage();

        $repeatPassword->addValidator(
            new Identical([
                'allowEmpty' => true,
                'value'      => $password->getValue(),
                'message'    => $translationMessage->_('passwords-not-equal')
            ])
        );

        
        $profileImage->addValidator(
            new FileValidator(
                [
                    'allowEmpty'     => true,
                    'maxSize'        => '2M',
                    'allowedTypes'   => [
                        'image/jpeg',
                        'image/png',
                    ],
                    'message'         => $translationMessage->_('allowed-files-are') . ':types'
                ]
            )
        );        

        $this->add($email);
        $this->add($password);
        $this->add($repeatPassword);
        $this->add($profileImage);
    }
}
