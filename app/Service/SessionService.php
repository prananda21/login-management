<?php

namespace PranandaYoga\LoginManagement\Service;

use PranandaYoga\LoginManagement\Domain\Session;
use PranandaYoga\LoginManagement\Domain\User;
use PranandaYoga\LoginManagement\Repository\SessionRepository;
use PranandaYoga\LoginManagement\Repository\UserRepository;

class SessionService
{
    public static string $COOKIE_NAME = "X-PRN-SESSION";
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    public function __construct(SessionRepository $sessionRepository, UserRepository $userRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
    }

    public function create(string $userId): Session
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = $userId;

        $this->sessionRepository->save($session);
        setcookie(self::$COOKIE_NAME, $session->id, time() + (60 * 60 * 24), "/");
        return $session;
    }

    public function destroy()
    {
        $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? "";
        $this->sessionRepository->deleteById($sessionId);

        setcookie(self::$COOKIE_NAME, ""); // expires 1 artinya masa lampau (detik pertama)
    }

    public function current(): ?User
    {
        $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? "";
        $session = $this->sessionRepository->findById($sessionId);
        if ($session == null) {
            return null;
        }

        return $this->userRepository->findById($session->userId);
    }
}
