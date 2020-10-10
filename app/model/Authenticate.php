<?php
declare(strict_types=1);

namespace App;

use Nette\Database\Context;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Utils\Strings;

/**
 * User authentication.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
class Authenticate implements IAuthenticator
{
    use Nette\SmartObject;
    
    public Context $connection;

    function __construct(Context $connection)
    {
        $this->connection = $connection;
    }

    function authenticate(array $credentials): IIdentity
    {
        list($username, $password) = $credentials;
        $username = Strings::lower($username);
        $row = $this->connection->table('users')
            ->where('username', $username)->fetch();

        if (!$row) {
            throw new AuthenticationException('User not found.');
        }

        $authenticate = new Passwords();
        if (!$authenticate->verify($password, $row->password)) {
            throw new AuthenticationException('Invalid password.');
        }

        return new Identity($row->uname, $row->roles);
    }
}