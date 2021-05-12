<?php
declare(strict_types=1);

namespace Phlexus\Modules\BaseUser\Controllers;

use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Controller;
use Phlexus\Modules\BaseUser\Form\LoginForm;
use Phlexus\Modules\BaseUser\Form\RemindForm;
use Phlexus\Modules\BaseUser\Models\Users;
use Phlexus\Helpers;

/**
 * Class AuthController
 *
 * @package Phlexus\Modules\BaseUser\Controllers
 */
class AuthController extends Controller
{
    /**
     * Login page
     *
     * @return void
     */
    public function loginAction(): void
    {
        $this->tag->setTitle('Phlexus CMS');
        $this->view->setMainView('layouts/base');

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

        $email = $post['email'];
        $password = $post['password'];

        $user = Users::findFirstByEmail($email);

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
        $this->tag->setTitle('Phlexus CMS');
        $this->view->setMainView('layouts/base');

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

        $user = Users::findFirstByEmail($email);

        if (!$user || $user->hash_code !== null) {
            return $this->response->redirect('user/auth');
        }

        $hash_code = $this->security->getRandom()->base64Safe(40);

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
     * @return void
     */
    public function recoverAction(string $hash_code) {
        //@ToDo Finish recover logic
        exit('test');
    }

    /**
     * Send Remind Email
     * 
     * @param Users $users Users model
     * @param string $code Hash Code
     *
     * @return bool
     */
    private function sendRemindEmail(Users $user, string $code) {
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
