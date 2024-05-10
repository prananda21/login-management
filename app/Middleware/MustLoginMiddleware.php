<?php

namespace PranandaYoga\LoginManagement\Middleware;

use PranandaYoga\LoginManagement\App\View;
use PranandaYoga\LoginManagement\Config\Database;
use PranandaYoga\LoginManagement\Repository\SessionRepository;
use PranandaYoga\LoginManagement\Repository\UserRepository;
use PranandaYoga\LoginManagement\Service\SessionService;

class MustLoginMiddleware implements Middleware
{
    private SessionService $sessionService;
    public function __construct()
    {
        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }
    function before(): void
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::redirect("/users/login");
        }
    }
}
