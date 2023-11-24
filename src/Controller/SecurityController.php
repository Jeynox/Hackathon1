<?php

namespace App\Controller;

use App\Model\SecurityManager;
use App\Controller\AbstractController;

class SecurityController extends AbstractController
{
    public function login()
    {
        $errors = [];
        if (isset($_SESSION['islogin']) && $_SESSION['islogin'] === true) {
            header('Location:/');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['email'] == '') {
                $errors['email'] = 'Veuillez renseigner votre email';
            }

            if ($_POST['password'] == '') {
                $errors['password'] = 'Veuillez renseigner votre password';
            }


            if (!$errors) {
                $securityManager = new SecurityManager();
                $user = $securityManager->userLogin($_POST);

                if ($user && password_verify($_POST['password'], $user['password'])) {
                    $_SESSION['islogin'] = true;
                    $_SESSION['email'] = $user['email'];
                    header('Location:/');
                } else {
                    $errors['login'] = 'Email ou mot de passe incorrect';
                }
            }
        }
        return $this->twig->render('Security/login.html.twig', ['errors' => $errors]);
    }

    public function logout()
    {
        unset($_SESSION['islogin']);
        unset($_SESSION['email']);
        header('Location:/');
    }

    public function verify()
    {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['email'] != '' && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Veuillez renseigner un email valide';
            }
            $securityManager = new SecurityManager();
            if ($securityManager->emailExist($_POST['email'])) {
                $errors['compte'] = 'Un compte existe deja avec cet email';
            }
            $requireds = ["firstname", "email", "password"];
            foreach ($requireds as $required) {
                if ($_POST[$required] == '') {
                    $errors[$required] = 'Ce champ est obligatoire';
                }
            }
            return $errors;
        }
    }

    public function signin()
    {
        $errors = $this->verify();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_SESSION['islogin']) && $_SESSION['islogin'] === true) {
                header('Location:/');
                return;
            }

            if ($errors) {
                return json_encode(['errorSignin' => $errors]);
            }


                $securityManager = new SecurityManager();
            if ($securityManager->userSignin($_POST)) {
                return json_encode(['status_signin' => 'success', 'message_success' => 'Inscription réussie']);
            } else {
                return json_encode(['status_signin' => 'success', 'message_success' => 'Inscription réussie']);
            }
        }
    }

    public function resetPassword()
    {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['email'] == '') {
                $errors['email'] = 'Veuillez renseigner votre email';
            }


            if (!$errors) {
                $securityManager = new SecurityManager();
                $user = $securityManager->userResetPassword($_POST);
                if ($user) {
                    $_SESSION['islogin'] = true;
                    $_SESSION['email'] = $user['email'];
                    header('Location:/');
                } else {
                    $errors['login'] = 'Email ou mot de passe incorrect';
                }
            }
        }
        return $this->twig->render('Security/forgot.html.twig', ['errors' => $errors]);
    }
}
