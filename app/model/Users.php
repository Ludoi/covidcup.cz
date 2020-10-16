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

    protected ?string $tableName = 'users';
    
    public function getUser(string $email): ?ActiveRow {
        return $this->findOneBy(['email' => Strings::lower($email)]);
    }

    public function checkPassword(string $email, string $password): bool {
        $user = $this->findOneBy(['email' => $email]);
        if (!is_null($user)) {
            $pwd = new Passwords();
            return $pwd->verify($password, (string)$user->hash);
        } else {
            return false;
        }
    }

    public function setPassword(string $email, string $password) {
        $now = new DateTime;
        $pwd = new Passwords();
        $hash = $pwd->hash($password);
        $this->findOneBy(['email' => Strings::lower($email)])->update(array('hash' => $hash, 'forgotten' => false,
            'forgotten_requested' => null, 'initial' => null, 'updated' => $now));
    }

    public function activate(string $email, bool $activate) {
        $now = new DateTime;
        $this->findOneBy(['email' => Strings::lower($email)])->update(['active' => $activate, 'updated' => $now,
            'initial' => null, 'token' => null]);
    }

    public function remove(string $email) {
        $this->findOneBy(['email' => Strings::lower($email)])->delete();
    }

    public function forgottenPassword(string $email): string {
        $now = new DateTime;
        $initial = Random::generate(100);
        $token =  Random::generate(100);
        $this->findOneBy(['email' => Strings::lower($email)])->update(['forgotten' => true, 'forgotten_requested' => $now,
            'initial' => $initial, 'token' => $token]);
        return $initial;
    }

    public function newUser(string $email, string $nickname, string $name, string $surname, int $year, string $gender,
                            string $roles, bool $active): array {
        $now = new DateTime;
        $password = Random::generate(20);
        $initial = Random::generate(100);
        $pwd = new Passwords();
        $hash = $pwd->hash($password);
        $data = array('email' => Strings::lower($email), 'firstname' => Strings::capitalize($name),
            'lastname' => Strings::capitalize($surname), 'nickname' => Strings::capitalize($nickname),
            'year' => $year, 'gender' => $gender, 'roles' => $roles, 'initial' => $initial,
            'created' => $now, 'hash' => $hash, 'active' => $active);
        $this->insert($data);
        return [ $password, $initial ];
    }

}
