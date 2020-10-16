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
    use \Nette\SmartObject;
    
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
            ->where('email', $username)->fetch();

        if (is_null($row)) {
            throw new AuthenticationException('Neplatné přihlášení.');
        }

        $authenticate = new Passwords();
        if (!$authenticate->verify($password, $row->hash)) {
            throw new AuthenticationException('Neplatné přihlášení.');
        }

        return new Identity($row->email, $row->roles);
    }
}