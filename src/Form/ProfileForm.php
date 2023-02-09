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
use Phlexus\Libraries\Media\Files\MimeTypes;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\File;
use Phalcon\Filter\Validation\Validator\Identical;
use Phalcon\Filter\Validation\Validator\Regex;
use Phalcon\Filter\Validation\Validator\File as FileValidator;

class ProfileForm extends CaptchaForm
{
    /**
     * Initialize form
     */
    public function initialize()
    {
        $translationForm = $this->translation->setPage()->setTypeForm();   

        $email = new Email('email', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-email-address'),
        ]);

        $oldPassword = new Password('old_password', [
            'class'       => 'form-control',
            'placeholder' => $translationForm->_('field-old-password'),
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

        $translationMessage = $this->translation->setTypeMessage();

        $passwordValue = $password->getValue();

        // allowEmpty not working
        if (!empty($passwordValue)) {
            $password->addValidator(
                new Regex(
                    [
                        'allowEmpty' => true,
                        'pattern'    => User::PASSWORD_REGEX,
                        'message'    => $translationMessage->_('weak-password'),
                    ]
                )
            );
        }

        $repeatPassword->addValidator(
            new Identical([
                'allowEmpty' => true,
                'value'      => $passwordValue,
                'message'    => $translationMessage->_('passwords-not-equal')
            ])
        );

        
        $profileImage->addValidator(
            new FileValidator(
                [
                    'allowEmpty'   => true,
                    'maxSize'      => '2M',
                    'allowedTypes' => MimeTypes::IMAGES,
                    'message'      => $translationMessage->_('allowed-files-are') . ':types'
                ]
            )
        );

        $this->add($email);
        $this->add($oldPassword);
        $this->add($password);
        $this->add($repeatPassword);
        $this->add($profileImage);
    }
}
