<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

use Phlexus\Forms\CaptchaForm;
use Phlexus\Modules\BaseUser\Models\User;
use Phlexus\Modules\BaseUser\Form\ProfileForm;
use Phlexus\Modules\BaseUser\Controllers\AbstractController;
use Phlexus\Libraries\Media\Models\Media;
use Phlexus\Libraries\Media\Files\MimeTypes;
use Phalcon\Tag;
use Exception;

/**
 * Class Profile
 *
 * @package Phlexus\Modules\BaseUser\Controllers
 */
final class ProfileController extends AbstractController
{
    /**
     * Initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        $title = $this->translation->setTypePage()->_('title-user-profile');

        Tag::setTitle($title);
    }

    /**
     * Edit page
     *
     * @return mixed ResponseInterface or void
     */
    public function editAction()
    {
        $profileForm = new ProfileForm();

        $user = User::getUser();

        if ($user === null) {
            return $this->response->redirect('/');
        }

        $user->old_password = '';
        $user->password = '';
        $user->repeat_password = '';

        $profileForm->setEntity($user);

        // @ToDo: Change to a url helper
        $refererURL = $this->request->getHttpReferer();
        $parsedUrl  = parse_url($refererURL);

        $this->view->setVar('defaultRoute', $parsedUrl['path'] ?? '/');
        $this->view->setVar('form', $profileForm);
    }


    /**
     * Edit page
     *
     * @return mixed ResponseInterface or void
     */
    public function saveAction()
    {
        $profileForm = new ProfileForm(false);

        $user = User::getUser();

        if ($user === null) {
            return $this->response->redirect('/');
        }

        $post = $this->request->getPost();

        if (!$post) {
            return $this->response->redirect('/profile');
        }

        $authorized = [
            'old_password',
            'password',
            'repeat_password',
            'profile_image',
            'csrf',
            CaptchaForm::CAPTCHA_NAME
        ];

        $authorizedKeys = array_flip($authorized);
        $oldPassword    = $user->password;

        $passwordChange = true;
        if (isset($post['password']) && empty($post['password'])) {
            $passwordChange   = false;
            $post['password'] = $oldPassword;
        }

        $profileForm->bind(array_intersect_key($post, $authorizedKeys), $user);

        $translationMessage = $this->translation->setPage()->setTypeMessage();

        if (!$profileForm->isValid()) {
            foreach ($profileForm->getMessages() as $message) {
                $this->flash->error($message->getMessage());
            }

            return $this->response->redirect('/profile');
        }

        // Remove csrf content, old_password, repeat_password and profile_image
        $user->csrf            = null;
        $user->old_password    = null;
        $user->repeat_password = null;
        $user->profile_image   = null;

        $hasFiles = $this->request->hasFiles() === true;

        $media = null;
        if ($hasFiles) {
            $media = $this->processUploadImage();
        }

        if ($media === null && $hasFiles) {
            $this->flash->error($translationMessage->_('unable-to-save-image'));

            return $this->response->redirect('/profile');
        } elseif ($media instanceof Media) {
            $user->imageID = (int) $media->id;
        }

        if ($passwordChange && !$this->security->checkHash($post['old_password'], $oldPassword)) {
            $this->flash->error($translationMessage->_('old-password-not-equal'));

            return $this->response->redirect('/profile');
        }

        if (!$user->save()) {
            $this->flash->error($translationMessage->_('record-not-saved'));

            return $this->response->redirect('/profile');
        }

        $this->flash->success($translationMessage->_('record-saved-sucessfully'));

        return $this->response->redirect('/profile');
    }

    /**
     * Process Upload Image
     *
     * @return mixed null if no file, Media if success or false if fails
     */
    private function processUploadImage(): ?Media
    {
        if ($this->request->hasFiles() !== true) {
            return null;
        }

        $files = $this->request->getUploadedFiles(true, true);
            
        if (isset($files['profile_image'])) { 
            $uploader = $this->uploader;   
            
            try {
                $media = $uploader->setFile($files['profile_image'])
                                ->setAllowedMimeTypes(MimeTypes::IMAGES)
                                ->uploadMedia();
            } catch (Exception $e) {
                return null;
            }

            return $media;
        }
    }
}
