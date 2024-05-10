<?php

namespace PranandaYoga\LoginManagement\Controller;

use PHPUnit\Framework\TestCase;
use PranandaYoga\LoginManagement\Config\Database;
use PranandaYoga\LoginManagement\Domain\Session;
use PranandaYoga\LoginManagement\Domain\User;
use PranandaYoga\LoginManagement\Repository\SessionRepository;
use PranandaYoga\LoginManagement\Repository\UserRepository;
use PranandaYoga\LoginManagement\Service\SessionService;

class HomeControllerTest extends TestCase
{
    private HomeController $homeController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $this->homeController = new HomeController();
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }
    public function testGuest()
    {
        $this->homeController->index();

        self::expectOutputRegex("[Login Management]");
    }
    public function testUserLogin()
    {
        $user = new User();
        $user->id = "yoga";
        $user->name = "Prananda Yoga";
        $user->password = "rahasia";
        $this->userRepository->save($user);

        $session = new Session();
        $session->id = uniqid();
        $session->userId = $user->id;
        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->homeController->index();
        self::expectOutputRegex("[Hello, Prananda Yoga]");
    }
}
