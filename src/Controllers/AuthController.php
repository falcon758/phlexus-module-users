<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Controller;
use Phalcon\Tag;
use Phlexus\Modules\BaseUser\Form\RegisterForm;
use Phlexus\Modules\BaseUser\Form\LoginForm;
use Phlexus\Modules\BaseUser\Form\RemindForm;
use Phlexus\Modules\BaseUser\Form\RecoverForm;
use Phlexus\Modules\BaseUser\Models\User;
use Phlexus\Libraries\Helpers;

/**
 * Class AuthController
 *
 * @package Phlexus\Modules\BaseUser\Controllers
 */
class AuthController extends Controller
{
    public function initialize(): void
    {
        $this->view->setMainView('layouts/base');
    }

    /**
     * Make register page
     *
     * @return void
     */
    public function createAction(): void
    {
        $title = $this->translation->setTypePage()->_('title-user-register');

        Tag::setTitle($title);

        $this->view->setVar('form', new RegisterForm());
    }

    /**
     * Make register POST request handler
     *
     * @return ResponseInterface
     */
    public function doCreateAction(): ResponseInterface
    {
        $this->view->disable();

        $translationMessage = $this->translation->setTypeMessage();

        if (!$this->request->isPost()) {
            $this->flash->error($translationMessage->_('invalid-data-sent'));

            return $this->response->redirect('user/auth/create');
        }

        $form = new RegisterForm(false);

        $post = $this->request->getPost();

        if (!$form->isValid($post)) {
            foreach ($form->getMessages() as $message) {
                $this->flash->error($message->getMessage());
            }

            return $this->response->redirect('user/auth/create');
        }

        $user = User::findFirstByEmail((string) $post['email']);

        // Email already registered
        if ($user) {
            $this->flash->error($translationMessage->_('email-already-exists'));

            return $this->response->redirect('user/auth/create');
        }

        $newUser = User::createUser($post['email'], $post['password']);

        if (!$newUser) {
            $this->flash->error($translationMessage->_('record-not-created'));

            return $this->response->redirect('user/auth/create');
        }

        if (
            !$this->sendActivateEmail(
                $newUser,
                $this->security->getUserTokenByHour($newUser->userHash),
                $newUser->hashCode
            )
        ) {
            $newUser->delete();

            return $this->response->redirect('user/auth/create');
        }

        $this->flash->success($translationMessage->_('account-created-successfully'));

        return $this->response->redirect('user/auth');
    }


    /**
     * Activate user
     * 
     * @param string $code Hash Code
     *
     * @return ResponseInterface|void
     * 
     * @ToDo: Restrict number of requests by ip to prevent hash brute force
     */
    public function activateAction(string $hashCode) {
        $translator = $this->translation;

        $title = $translator->setTypePage()->_('title-user-activation');

        Tag::setTitle($title);

        $user = User::getActivateUser($hashCode);

        $security = $this->security;

        $token = (string) $this->request->get('token', null, '');

        $translationMessage = $translator->setTypeMessage();

        // Assure that hash code exists
        if (!$user || !$security->checkUserTokenByHour($token, $user->userHash)) {
            $this->flash->error($translationMessage->_('unable-to-activate-account'));

            return $this->response->redirect('user/auth/create');
        }

        if (!$user->activateUser()) {
            $this->flash->error($translationMessage->_('unable-to-activate-account'));

            return $this->response->redirect('user/auth/create');
        }

        $this->flash->success($translationMessage->_('account-activated-successfully'));

        return $this->response->redirect('user/auth');
    }

    /**
     * Login page
     *
     * @return void
     */
    public function loginAction(): void
    {
        $title = $this->translation->setTypePage()->_('title-user-login');

        //Tag::setTitle($title);

        $this->view->setVar('form', new LoginForm());
    }

    /**
     * Login POST request handler
     *
     * @return ResponseInterface
     */
    public function doLoginAction(): ResponseInterface
    {
        $this->view->disable();

        if (!$this->request->isPost()) {
            return $this->response->redirect('user/auth');
        }

        $form = new LoginForm(false);

        $post = $this->request->getPost();

        if (!$form->isValid($post)) {
            foreach ($form->getMessages() as $message) {
                $this->flash->error($message->getMessage());
            }

            return $this->response->redirect('user/auth');
        }

        $email    = $post['email'];
        $password = $post['password'];

        $user = User::findFirstByEmail($email);

        $login = $this->auth->login([
            'email' => $email,
            'password' => $password,
        ]);
        
        if ($login === false) {
            if ($user !== null) {
                $user->failedLogin();
            }

            $loginFailed = $this->translation->setTypeMessage()->_('login-failed');
            $this->flash->error($loginFailed);

            return $this->response->redirect('user/auth');
        }

        $user->successfullLogin();

        return $this->response->redirect($this->auth->getloginRedirect());
    }

    /**
     * Logout POST request handler
     *
     * @return ResponseInterface
     */
    public function logoutAction(): ResponseInterface
    {
        $this->view->disable();

        if ($this->auth->isLogged()) {
            $this->auth->logout();
        }

        return $this->response->redirect('/');
    }

    /**
     * Remind page
     *
     * @return void
     */
    public function remindAction(): void
    {
        $title = $this->translation->setTypePage()->_('title-password-reminder');

        Tag::setTitle($title);

        $this->view->setVar('form', new RemindForm());
    }

    /**
     * Remind POST request handler
     *
     * @return ResponseInterface
     */
    public function doRemindAction(): ResponseInterface
    {
        $this->view->disable();

        if (!$this->request->isPost()) {
            return $this->response->redirect('user/auth/remind');
        }

        $form = new RemindForm(false);

        $post = $this->request->getPost();

        $translationMessage = $this->translation->setTypeMessage();

        if (!$form->isValid($post)) {
            foreach ($form->getMessages() as $message) {
                $this->flash->error($message->getMessage());
            }

            return $this->response->redirect('user/auth/remind');
        }

        $email = $post['email'];

        $user = User::findFirstByEmail($email);

        if (!$user || !isset($user->hashCode)) {
            $this->flash->error($translationMessage->_('reminder-not-processed'));

            return $this->response->redirect('user/auth');
        }

        $user->generateHashCode();

        if (!$this->sendRemindEmail($user, $this->security->getUserTokenByHour($user->userHash), $user->hashCode)) {
            return $this->response->redirect('user/auth/remind');
        }

        $this->flash->success($translationMessage->_('confirm-email-to-recover'));

        return $this->response->redirect('user/auth');
    }

    /**
     * Recover user password
     * 
     * @param string $code Hash Code
     *
     * @return ResponseInterface|void
     * 
     */
    public function recoverAction(string $hashCode)
    {
        $translator = $this->translation;

        $title = $translator->setTypePage()->_('title-password-recover');

        Tag::setTitle($title);

        $user = User::findByHashCode($hashCode);

        $security = $this->security;

        $token = (string) $this->request->get('token', null, '');

        // Assure that only one hash is found and token is correct
        if (count($user) !== 1 || !$security->checkUserTokenByHour($token, $user[0]->userHash)) {
            $errorMessage = $translator->setTypeMessage()
                                       ->_('unable-to-process-recover');

            $this->flash->error($errorMessage);

            return $this->response->redirect('user/auth/remind');
        }

        $recoverForm = new RecoverForm();

        $recoverForm->get('hash_code')->setDefault($hashCode);

        $this->view->setVar('form', $recoverForm);
    }

    /**
     * Recover POST request handler
     *
     * @return ResponseInterface
     * 
     */
    public function doRecoverAction(): ResponseInterface
    {
        $this->view->disable();

        $translationMessage = $this->translation->setTypeMessage();

        if (!$this->request->isPost()) {
            $this->flash->error($translationMessage->_('invalid-data-sent'));

            return $this->response->redirect('user/auth/remind');
        }

        $form = new RecoverForm(false);

        $post = $this->request->getPost();

        if (!$form->isValid($post)) {
            foreach ($form->getMessages() as $message) {
                $this->flash->error($message->getMessage());
            }

            return $this->response->redirect($this->request->getHttpReferer());
        }

        $hashCode = $post['hash_code'];
        $user     = User::findByHashCode($hashCode);

        // Assure that only one hash is found
        if (count($user) !== 1) {
            $this->flash->error($translationMessage->_('unable-to-process-recover'));

            return $this->response->redirect('user/auth/remind');
        }

        $user = $user[0];

        if (!$user->changePassword($post['password'])) {
            $this->flash->error($translationMessage->_('unable-to-process-recover'));

            return $this->response->redirect('user/auth/remind');
        }

        $this->flash->success($translationMessage->_('account-recovered-successfully'));

        return $this->response->redirect('user/auth');
    }

    /**
     * Send Activate Email
     * 
     * @param User   $user      User model
     * @param string $userToken User Token
     * @param string $code      Hash Code
     *
     * @return bool
     */
    private function sendActivateEmail(User $user, string $userToken, string $code) {
        $url = $this->url->get('user/auth/activate/' . $code, [
            'token' => $userToken
        ]);

        try {
            $body = Helpers::renderEmail($this->view, 'auth', 'activate', ['url' => $url]);
        } catch(\Exception $e) {
            $errorMessage = $this->translation->setTypeMessage()
                                             ->_('activation-failed');

            $this->flash->error($errorMessage);

            return false;
        }

        return Helpers::sendEmail($user->email, 'Account Activation', $body);
    }

    /**
     * Send Remind Email
     * 
     * @param User   $user      User model
     * @param string $userToken User Token
     * @param string $code      Hash Code
     *
     * @return bool
     */
    private function sendRemindEmail(User $user, string $userToken, string $code) {
        $url = $this->url->get('user/auth/recover/' . $code, [
            'token' => $userToken
        ]);

        try {
            $body = Helpers::renderEmail($this->view, 'auth', 'remind', ['url' => $url]);
        } catch(\Exception $e) {

            $errorMessage = $this->translation->setTypeMessage()
                                              ->_('reminder-process-failed');

            $this->flash->error($errorMessage);

            return false;
        }

        return Helpers::sendEmail($user->email, 'Password Reminder', $body);
    }
}
