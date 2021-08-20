<?php


namespace Nacho\Security;


interface UserHandlerInterface
{
    public function getCurrentUser();

    public function getUsers();

    public function findUser(string $username);

    public function changePassword(string $oldPassword, string $newPassword);

    public function logout();

    public function isGranted(string $minRight = 'Guest', ?array $user = null);
}