<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Controller;
use Phlexus\Modules\BaseUser\Form\RegisterForm;
use Phlexus\Modules\BaseUser\Form\LoginForm;
use Phlexus\Modules\BaseUser\Form\RemindForm;
use Phlexus\Modules\BaseUser\Form\RecoverForm;
use Phlexus\Modules\BaseUser\Models\User;
use Phlexus\Modules\BaseUser\Models\Profile;
use Phlexus\Helpers;

/**
 * Class AuthController
 *
 * @package Phlexus\Modules\BaseUser\Controllers
 */
class AuthController extends Controller
{
    const HASHLENGTH = 40;

    public function initialize(): void
    {
        $this->tag->setTitle('Phlexus CMS');
        $this->view->setMainView('layouts/base');
    }

    /**
     * Make register page
     *
     * @return void
     */
    public function createAction(): void
    {
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

        if (!$this->request->isPost()) {
            return $this->response->redirect('user/auth/create');
        }

        $form = new RegisterForm(false);

        $post = $this->request->getPost();

        if (!$form->isValid($post)) {
            return $this->response->redirect('user/auth/create');
        }

        $user = User::findFirstByEmail($email);

        // Email already registered
        if ($user) {
            return $this->response->redirect('user/auth/create');
        }

        $new_user            = new User();
        $new_user->email     = $post['email'];
        $new_user->password  = $post['password'];
        $new_user->active    = User::DISABLED;
        $new_user->profileId = Profile::MEMBERID;

        $hash_code = $this->security->getRandom()->base64Safe(self::HASHLENGTH);
        $new_user->hash_code = $hash_code;

        if (!$new_user->save()) {
            return $this->response->redirect('user/auth/create');
        }

        if (!$this->sendActivateEmail($user, $hash_code)) {
            $new_user->delete();

            return $this->response->redirect('user/auth/create');
        }

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
    public function activateAction(string $hash_code) {
        $user = User::findFirst([
            'conditions' => "active = :active: AND hash_code = :hash_code:",
            'bind'       => [
                'active'  => User::DISABLED,
                'hash_code'  => $hash_code
            ],
        ]);

        // Assure that only one hash is found
        if (count($user) !== 1) {
            return $this->response->redirect('user/auth/create');
        }

        $user->status    = User::ENABLED;
        $user->hash_code = null;

        if (!$user->save()) {
            return $this->response->redirect('user/auth/create');
        }

        return $this->response->redirect('user/auth');
    }

    /**
     * Login page
     *
     * @return void
     */
    public function loginAction(): void
    {
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

            return $this->response->redirect('user/auth');
        }

        $user->successfullLogin();

        return $this->response->redirect('user');
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

        return $this->response->redirect('user/auth');
    }

    /**
     * Remind page
     *
     * @return void
     */
    public function remindAction(): void
    {
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

        if (!$form->isValid($post)) {
            return $this->response->redirect('user/auth/remind');
        }

        $email = $post['email'];

        $user = User::findFirstByEmail($email);

        if (!$user || $user->hash_code !== null) {
            return $this->response->redirect('user/auth');
        }

        $hash_code = $this->security->getRandom()->base64Safe(self::HASHLENGTH);

        $user->hash_code = $hash_code;

        $user->save();

        if (!$this->sendRemindEmail($user, $hash_code)) {
            return $this->response->redirect('user/auth/remind');
        }

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
    public function recoverAction(string $hash_code) {
        $user = User::findByHash_code($hash_code);

        // Assure that only one hash is found
        if (count($user) !== 1) {
            return $this->response->redirect('user/auth/remind');
        }

        $recover_form = new RecoverForm();

        $recover_form->get('hash_code')->setDefault($hash_code);

        $this->view->setVar('form', $recover_form);
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

        if (!$this->request->isPost()) {
            return $this->response->redirect('user/auth/remind');
        }

        $form = new RecoverForm(false);

        $post = $this->request->getPost();

        $hash_code = $post['hash_code'];

        if (!$form->isValid($post)) {
            return $this->response->redirect('user/auth/remind');
        }

        $user = User::findByHash_code($hash_code);

        // Assure that only one hash is found
        if (count($user) !== 1) {
            return $this->response->redirect('user/auth/remind');
        }

        $user = $user[0];

        $user->password  = $post['password'];
        $user->hash_code = null;

        if (!$user->save()) {
            return $this->response->redirect('user/auth/remind');
        }

        return $this->response->redirect('user/auth');
    }

    /**
     * Send Activate Email
     * 
     * @param User $user User model
     * @param string $code Hash Code
     *
     * @return bool
     */
    private function sendActivateEmail(User $user, string $code) {
        $url = $this->url->get('user/auth/activate', ['hash' => $code]);

        $body = $this->view->getPartial('emails/auth/activate', ['url' => $url]);

        $mail = $this->di->getShared('email');

        // If not inside Phlexus cms
        if (!$mail) {
            return false;
        }

        $mail->addAddress($user->email);
        $mail->Subject = 'Account Activation';
        $mail->Body    = $body;

        return $mail->send();
    }

    /**
     * Send Remind Email
     * 
     * @param User $user User model
     * @param string $code Hash Code
     *
     * @return bool
     */
    private function sendRemindEmail(User $user, string $code) {
        $url = $this->url->get('user/auth/recover', ['hash' => $code]);

        $body = $this->view->getPartial('emails/auth/remind', ['url' => $url]);

        $mail = $this->di->getShared('email');

        // If not inside Phlexus cms
        if (!$mail) {
            return false;
        }

        $mail->addAddress($user->email);
        $mail->Subject = 'Password Reminder';
        $mail->Body    = $body;

        return $mail->send();
    }
}
