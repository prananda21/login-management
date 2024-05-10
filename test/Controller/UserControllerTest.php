<?php

namespace PranandaYoga\LoginManagement\Controller {
    require_once __DIR__ . '/../Helper/helper.php';

    use PHPUnit\Framework\TestCase;
    use PranandaYoga\LoginManagement\Config\Database;
    use PranandaYoga\LoginManagement\Domain\Session;
    use PranandaYoga\LoginManagement\Domain\User;
    use PranandaYoga\LoginManagement\Repository\SessionRepository;
    use PranandaYoga\LoginManagement\Repository\UserRepository;
    use PranandaYoga\LoginManagement\Service\SessionService;

    class UserControllerTest extends TestCase
    {
        private UserController $userController;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->userController = new UserController();
            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();
            $this->userRepository = new UserRepository(Database::getConnection());
            $this->userRepository->deleteAll();

            putenv("mode=test");
        }

        // Register Controller
        public function testRegister()
        {
            $this->userController->register();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register new User]");
        }
        public function testPostRegisterSuccess()
        {
            $_POST['id'] = 'eko';
            $_POST['name'] = 'Eko';
            $_POST['password'] = 'rahasia';
            $this->userController->postRegister();

            $this->expectOutputRegex("[Location: /users/login]");
        }
        public function testPostRegisterValidationError()
        {
            $_POST['id'] = '';
            $_POST['name'] = 'Eko';
            $_POST['password'] = 'rahasia';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register new User]");
            $this->expectOutputRegex("[Id, Name, and Password cannot be blank!]");
        }
        public function testPostRegisterDuplicate()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = 'rahasia';

            $this->userRepository->save($user);

            $_POST['id'] = 'eko';
            $_POST['name'] = 'Eko';
            $_POST['password'] = 'rahasia';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register new User]");
            $this->expectOutputRegex("[User already exist!]");
        }

        // Login Controller
        public function testLogin()
        {
            $this->userController->login();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
        }
        public function testLoginSuccess()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = 'eko';
            $_POST['password'] = 'rahasia';

            $this->userController->postLogin();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-PRN-SESSION]");
        }
        public function testLoginValidationError()
        {
            $_POST['id'] = "";
            $_POST['password'] = "";
            $this->userController->postLogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id and Password cannot be blank!]");
        }
        public function testLoginUserNotFound()
        {
            $_POST['id'] = "notfound";
            $_POST['password'] = "notfound";
            $this->userController->postLogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[id or password is wrong]");
        }
        public function testLoginWrongPassword()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);

            $_POST['id'] = "eko";
            $_POST['password'] = "notfound";
            $this->userController->postLogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[id or password is wrong]");
        }
        public function testLogout()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->logout();

            self::expectOutputRegex("[Location: /]");
            self::expectOutputRegex("[X-PRN-SESSION: ]");
        }

        public function testUpdateProfile()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);
            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updateProfile();

            self::expectOutputRegex("[Profile]");
            self::expectOutputRegex("[Id]");
            self::expectOutputRegex("[eko]");
            self::expectOutputRegex("[Name]");
            self::expectOutputRegex("[Eko]");
        }

        public function testPostUpdateProfileSuccess()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);
            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = "Budi";
            $this->userController->postUpdateProfile();

            self::expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById($user->id);
            self::assertEquals("Budi", $result->name);
        }
        public function testPostUpdateProfileValidationError()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);
            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = "";
            $this->userController->postUpdateProfile();

            self::expectOutputRegex("[Profile]");
            self::expectOutputRegex("[Id]");
            self::expectOutputRegex("[eko]");
            self::expectOutputRegex("[Name]");
            self::expectOutputRegex("[Id and Name cannot be blank!]");
        }

        public function testUpdatePassword()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);
            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updatePassword();
            self::expectOutputRegex("[Password]");
            self::expectOutputRegex("[Id]");
            self::expectOutputRegex("[eko]");
        }
        public function testPostUpdatePassword()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);
            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = 'rahasia';
            $_POST['newPassword'] = 'budi';

            $this->userController->postUpdatePassword();

            self::expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById($user->id);
            self::assertTrue(password_verify("budi", $result->password));
        }
        public function testPostUpdatePasswordValidationError()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);
            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = '';
            $_POST['newPassword'] = '';

            $this->userController->postUpdatePassword();

            self::expectOutputRegex("[Password]");
            self::expectOutputRegex("[Id]");
            self::expectOutputRegex("[eko]");
            self::expectOutputRegex("[Id, Old Password, and New Password cannot be blank!]");
        }
        public function testPostUpdatePasswordWrongOldPassword()
        {
            $user = new User();
            $user->id = 'eko';
            $user->name = 'Eko';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);
            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = 'salah';
            $_POST['newPassword'] = 'saya';

            $this->userController->postUpdatePassword();

            self::expectOutputRegex("[Password]");
            self::expectOutputRegex("[Id]");
            self::expectOutputRegex("[eko]");
            self::expectOutputRegex("[Old password is wrong!]");
        }
    }
}
