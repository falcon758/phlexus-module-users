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
        $email = new Email('email', [
            'class'       => 'form-control',
            'placeholder' => 'Email',
            'readonly'    => true
        ]);
        
        $password = new Password('password', [
            'class'       => 'form-control',
            'placeholder' => 'Password',
        ]);

        $repeat_password = new Password('repeat_password', [
            'class'       => 'form-control',
            'placeholder' => 'Repeat-Password',
        ]);

        $profile_image = new File('profile_image');
                
        $password_value = $password->getValue();

        $repeat_password->addValidator(
            new Identical([
                'allowEmpty' => true,
                'value'      => $password->getValue(),
                'message'    => 'Passwords not equal'
            ])
        );

        $profile_image->addValidator(
            new FileValidator(
                [
                    'allowEmpty'     => true,
                    'maxSize'        => '2M',
                    'allowedTypes'   => [
                        'image/jpeg',
                        'image/png',
                    ],
                    'message'         => 'Allowed file types are :types'
                ]
            )
        );        

        $this->add($email);
        $this->add($password);
        $this->add($repeat_password);
        $this->add($profile_image);
    }
}
