<?php
/**
 * Created by PhpStorm.
 * User: bajza
 * Date: 15.03.2018
 * Time: 0:10
 */

namespace App\Model;

use App\Manager\UserManager;
use Nette\Security as NS;

class Authenticator implements NS\IAuthenticator
{
    private $db;
    private $userManager;

    public function __construct(\Dibi\Connection $db, UserManager $userManager)
    {
        $this->db = $db;
        $this->userManager = $userManager;
    }

    function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        $row = $this->userManager->getUserByEmail($email);

        if (!$row) {
            throw new NS\AuthenticationException("User '$email' not found.", self::IDENTITY_NOT_FOUND);
        }

        if ($row->password !== sha1($password . UserManager::USER_SALT)) {
            throw new NS\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
        }

        unset($row->password);
        return new NS\Identity($row->id, $row->role, $row->toArray());
    }
}