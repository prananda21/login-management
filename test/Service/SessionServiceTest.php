<?php

namespace PranandaYoga\LoginManagement\Service;

require_once __DIR__ . '/../Helper/helper.php';

use PHPUnit\Framework\TestCase;
use PranandaYoga\LoginManagement\Config\Database;
use PranandaYoga\LoginManagement\Domain\Session;
use PranandaYoga\LoginManagement\Domain\User;
use PranandaYoga\LoginManagement\Repository\SessionRepository;
use PranandaYoga\LoginManagement\Repository\UserRepository;

class SessionServiceTest extends TestCase
{

    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();

        $user = new User();
        $user->id = "yoga21";
        $user->name = "Yoga";
        $user->password = "rahasia";
        $this->userRepository->save($user);
    }

    public function testCreate()
    {
        $session = $this->sessionService->create("yoga21");
        self::expectOutputRegex("[X-PRN-SESSION: $session->id]");

        $result = $this->sessionRepository->findById($session->id);
        self::assertEquals("yoga21", $result->userId);
    }
    public function testDestroy()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "yoga21";

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->sessionService->destroy();
        self::expectOutputRegex("[X-PRN-SESSION:]");

        // $result = $this->sessionRepository->findById($session->id);
        // self::assertNull($result);
    }
    public function testCurrent()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "yoga21";

        $this->sessionRepository->save($session);
        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $user = $this->sessionService->current();
        self::assertEquals($session->userId, $user->id);
    }
}
