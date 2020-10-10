<?php
declare(strict_types=1);

namespace App;

use Nette\Database\Table\ActiveRow;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Nette\Utils\Random;
use Nette\Utils\Strings;

/**
 * Users maintenance.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
class Users extends Table {

    protected string $tableName = 'users';
    
    public function getUser(string $user): ActiveRow {
        $username = Strings::lower($user);        
        return $this->find($username);
    }

    public function setPassword(string $user, string $password) {
        $username = Strings::lower($user);
        $now = new DateTime;
        $pwd = new Passwords();
        $hash = $pwd->hash($password);
        $this->find($username)->update(array('password' => $hash, 'forgotten' => false,
            'forgotten_requested' => null, 'initial' => null, 'updated' => $now));
    }

    public function activate(string $user, bool $activate) {
        $username = Strings::lower($user);
        $now = new DateTime;
        $this->find($username)->update(array('active' => $activate, 'updated' => $now));
    }

    public function forgottenPassword(string $user) {
        $username = Strings::lower($user);
        $now = new DateTime;
        $initial = Random::generate(50);
        $this->find($username)->update(array('forgotten' => true, 'forgotten_requested' => $now,
            'initial' => $initial));
    }

    public function newUser(string $user, string $name, string $surname, string $email, int $year, string $gender, string $roles): string {
        $username = Strings::lower($user);
        $now = new DateTime;
        $password = Random::generate(20);
        $pwd = new Passwords();
        $hash = $pwd->hash($password);
        $data = array('uname' => $username, 'firstname' => Strings::capitalize($name),
            'lastname' => Strings::capitalize($surname), 'email' => Strings::lower($email),
            'year' => $year, 'gender' => $gender, 'roles' => $roles,
            'created' => $now, 'password' => $hash, 'active' => true);
        $this->insert($data);
        return $password;
    }

}
