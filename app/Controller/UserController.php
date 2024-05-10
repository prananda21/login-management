<?php

namespace PranandaYoga\LoginManagement\Controller;

use PhpParser\Node\Stmt\TryCatch;
use PranandaYoga\LoginManagement\App\View;
use PranandaYoga\LoginManagement\Config\Database;
use PranandaYoga\LoginManagement\Exception\ValidationException;
use PranandaYoga\LoginManagement\Model\UserLoginRequest;
use PranandaYoga\LoginManagement\Model\UserPasswordUpdateRequest;
use PranandaYoga\LoginManagement\Model\UserProfileUpdateRequest;
use PranandaYoga\LoginManagement\Model\UserRegisterRequest;
use PranandaYoga\LoginManagement\Repository\SessionRepository;
use PranandaYoga\LoginManagement\Repository\UserRepository;
use PranandaYoga\LoginManagement\Service\SessionService;
use PranandaYoga\LoginManagement\Service\UserService;

class UserController
{
    private UserService $userService;
    private SessionService $sessionService;
    public  function __construct()
    {
        $connection = Database::getConnection();
        $userRepository = new UserRepository($connection);
        $this->userService = new UserService($userRepository);

        $sessionRepository = new SessionRepository($connection);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }
    public function register() // Menampilkan halaman registrasi
    {
        View::render('User/register', [
            'title' => 'Register new User',
        ]);
    }
    public function postRegister() // melakukan aksi registrasi
    {
        $request = new UserRegisterRequest();
        $request->id = $_POST['id'];
        $request->name = $_POST['name'];
        $request->password = $_POST['password'];


        try {
            //code...
            $this->userService->register($request);
            // success will redirect to /users/login
            View::redirect('/users/login');
        } catch (ValidationException $exception) {
            //throw $th;
            View::render('User/register', [
                'title' => 'Register new User',
                'error' => $exception->getMessage()
            ]);
        }
    }

    public function login()
    {
        View::render('User/login', [
            'title' => "Login user"
        ]);
    }

    public function postLogin()
    {
        $request = new UserLoginRequest();
        $request->id = $_POST['id'];
        $request->password = $_POST['password'];

        try {
            $response = $this->userService->login($request);
            $this->sessionService->create($response->user->id);
            View::redirect('/');
        } catch (ValidationException $exception) {
            //throw $th;
            View::render('User/login', [
                'title' => 'Login user',
                'error' => $exception->getMessage()
            ]);
        }
    }

    public function logout()
    {
        $this->sessionService->destroy();
        View::redirect("/");
    }

    public function updateProfile()
    {
        $user = $this->sessionService->current();
        View::render("User/profile", [
            "title" => "Update user profile",
            "user" => [
                "id" => $user->id,
                "name" => $user->name
            ]
        ]);
    }
    public function postUpdateProfile()
    {
        $user = $this->sessionService->current();
        $request = new UserProfileUpdateRequest();
        $request->id = $user->id;
        $request->name = $_POST['name'];

        try {
            $this->userService->updateProfile($request);
            View::redirect('/');
        } catch (ValidationException $exception) {
            View::render("User/profile", [
                "title" => "Update user profile",
                "error" => $exception->getMessage(),
                "user" => [
                    "id" => $user->id,
                    "name" => $_POST['name']
                ]
            ]);
        }
    }

    public function updatePassword()
    {
        $user = $this->sessionService->current();
        View::render("User/password", [
            "title" => "Update user password",
            "user" => [
                "id" => $user->id
            ]
        ]);
    }

    public function postUpdatePassword()
    {
        $user = $this->sessionService->current();
        $request = new UserPasswordUpdateRequest();
        $request->id = $user->id;
        $request->oldPassword = $_POST['oldPassword'];
        $request->newPassword = $_POST['newPassword'];

        try {
            $this->userService->updatePassword($request);
            View::redirect('/');
        } catch (ValidationException $exception) {
            View::render('User/password', [
                "title" => "Update user password",
                "error" => $exception->getMessage(),
                "user" => [
                    "id" => $user->id
                ]
            ]);
        }
    }
}
