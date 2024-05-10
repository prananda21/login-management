<?php


namespace PranandaYoga\LoginManagement\Middleware {
    require_once __DIR__ . "/../Helper/helper.php";

    use PranandaYoga\LoginManagement\Domain\User;
    use PHPUnit\Framework\TestCase;
    use PranandaYoga\LoginManagement\Config\Database;
    use PranandaYoga\LoginManagement\Domain\Session;
    use PranandaYoga\LoginManagement\Repository\SessionRepository;
    use PranandaYoga\LoginManagement\Repository\UserRepository;
    use PranandaYoga\LoginManagement\Service\SessionService;

    class MustNotLoginMiddlewareTest extends TestCase
    {
        private MustNotLoginMiddleware $middleware;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->middleware = new MustNotLoginMiddleware();
            putenv("mode=test");

            $this->userRepository = new UserRepository(Database::getConnection());
            $this->sessionRepository = new SessionRepository(Database::getConnection());

            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();
        }


        public function testBeforeGuest()
        {
            $this->middleware->before();
            self::expectOutputString("");
        }

        public function testBeforeMember()
        {
            $user = new User();
            $user->id = "eko";
            $user->name = "Eko";
            $user->password = "rahasia";
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->middleware->before();
            self::expectOutputRegex("[Location: /]");
        }
    }
}
