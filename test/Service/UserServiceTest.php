<?php

namespace PranandaYoga\LoginManagement\Service;

use PHPUnit\Framework\TestCase;
use PranandaYoga\LoginManagement\Config\Database;
use PranandaYoga\LoginManagement\Domain\User;
use PranandaYoga\LoginManagement\Exception\ValidationException;
use PranandaYoga\LoginManagement\Model\UserLoginRequest;
use PranandaYoga\LoginManagement\Model\UserPasswordUpdateRequest;
use PranandaYoga\LoginManagement\Model\UserProfileUpdateRequest;
use PranandaYoga\LoginManagement\Model\UserRegisterRequest;
use PranandaYoga\LoginManagement\Repository\SessionRepository;
use PranandaYoga\LoginManagement\Repository\UserRepository;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);
        $this->sessionRepository = new SessionRepository($connection);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testRegisterSuccess()
    {
        $request = new UserRegisterRequest();
        $request->id = "Yoga";
        $request->name = "Prananda Yoga";
        $request->password = "rahasia";

        $response = $this->userService->register($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertEquals($request->name, $response->user->name);
        // not equal karena password harus dihash sehingga harus berbeda dengan request
        self::assertNotEquals($request->password, $response->user->password);

        # memverify password yang sudah dihash
        self::assertTrue(password_verify($request->password, $response->user->password));
    }
    public function testRegisterFailed()
    {
        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "";
        $request->name = "";
        $request->password = "";

        $this->userService->register($request);
    }
    public function testRegisterDuplicate()
    {
        $user = new User();
        $user->id = "Eko";
        $user->name = "Eko";
        $user->password = "rahasia";

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "Eko";
        $request->name = "Eko";
        $request->password = "rahasia";

        $this->userService->register($request);
    }

    public function testLoginNotFound()
    {
        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();
        $request->id = "Eko";
        $request->password = "rahasia";

        $this->userService->login($request);
    }
    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->id = "eko";
        $user->name = "Eko";
        $user->password = password_hash("eko", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "Eko";
        $request->password = "rahasia";

        $this->userService->login($request);
    }
    public function testLoginSuccess()
    {
        $user = new User();
        $user->id = "eko";
        $user->name = "Eko";
        $user->password = password_hash("eko", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "Eko";
        $request->password = "eko";

        $response = $this->userService->login($request);
        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testUpdateSuccess()
    {
        $user = new User();
        $user->id = "Yoga";
        $user->name = "Prananda Yoga";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserProfileUpdateRequest();
        $request->id = "Yoga";
        $request->name = "Budi";

        $this->userService->updateProfile($request);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($request->name, $result->name);
    }
    public function testUpdateValidationError()
    {
        self::expectException(ValidationException::class);
        $user = new User();
        $user->id = "Yoga";
        $user->name = "Prananda Yoga";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserProfileUpdateRequest();
        $request->id = "";
        $request->name = "";

        $this->userService->updateProfile($request);
    }
    public function testUpdateUserNotFound()
    {
        self::expectException(ValidationException::class);
        $request = new UserProfileUpdateRequest();
        $request->id = "Yoga";
        $request->name = "Budi";

        $this->userService->updateProfile($request);
    }

    public function testUpdatePasswordSuccess()
    {
        $user = new User();
        $user->id = "Yoga";
        $user->name = "Prananda Yoga";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = "Yoga";
        $request->oldPassword = "rahasia";
        $request->newPassword = "tidakrahasia";

        $this->userService->updatePassword($request);

        $result = $this->userRepository->findById($user->id);
        self::assertTrue(password_verify($request->newPassword, $result->password));
    }
    public function testUpdatePasswordValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "Yoga";
        $request->oldPassword = "";
        $request->newPassword = "";

        $this->userService->updatePassword($request);
    }
    public function testUpdatePasswordWrongOldPassword()
    {
        self::expectException(ValidationException::class);
        $user = new User();
        $user->id = "Yoga";
        $user->name = "Prananda Yoga";
        $user->password = password_hash("rahasia", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = "Yoga";
        $request->oldPassword = "a";
        $request->newPassword = "tidakrahasia";

        $this->userService->updatePassword($request);
    }
    public function testUpdatePasswordNotFound()
    {
        self::expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "Yoga";
        $request->oldPassword = "rahasia";
        $request->newPassword = "tidakrahasia";

        $this->userService->updatePassword($request);
    }
}
