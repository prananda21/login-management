<?php

namespace PranandaYoga\LoginManagement\Repository;

use PHPUnit\Framework\TestCase;
use PranandaYoga\LoginManagement\Config\Database;
use PranandaYoga\LoginManagement\Domain\Session;
use PranandaYoga\LoginManagement\Domain\User;

class SessionRepositoryTest extends TestCase
{
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();

        $user = new User();
        $user->id = "yoga";
        $user->name = "Yoga";
        $user->password = "rahasia";
        $this->userRepository->save($user);
    }

    public function testSaveSuccess()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "Yoga";

        $this->sessionRepository->save($session);

        $result = $this->sessionRepository->findById($session->id);
        self::assertEquals($session->id, $result->id);
        self::assertEquals($session->userId, $result->userId);
    }
    public function testDeleteByIdSuccess()
    {
        // self::markTestIncomplete("Not Complete Yet");
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "Yoga";

        $this->sessionRepository->save($session);

        $result = $this->sessionRepository->findById($session->id);
        self::assertEquals($session->id, $result->id);
        self::assertEquals($session->userId, $result->userId);

        $this->sessionRepository->deleteById($session->id);
        $result = $this->sessionRepository->findById($session->id);

        self::assertNull($result);
    }
    public function testFindByIdNotFound()
    {
        // self::markTestIncomplete("Not Complete Yet");
        $result = $this->sessionRepository->findById('ulala');
        self::assertNull($result);
    }
}
