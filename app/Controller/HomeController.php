<?php

namespace PranandaYoga\LoginManagement\Controller;

use PranandaYoga\LoginManagement\App\View;
use PranandaYoga\LoginManagement\Config\Database;
use PranandaYoga\LoginManagement\Repository\SessionRepository;
use PranandaYoga\LoginManagement\Repository\UserRepository;
use PranandaYoga\LoginManagement\Service\SessionService;

class HomeController
{

    private SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $sessionRepository = new SessionRepository($connection);
        $userRepository = new UserRepository($connection);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }
    function index()
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::render("Home/index", [
                'title' => 'PHP Login Management'
            ]);
        } else {
            View::render("Home/dashboard", [
                'title' => 'Dashboard',
                "user" => [
                    "name" => $user->name
                ]
            ]);
        }
    }
}
