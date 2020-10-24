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
class UsersChanges extends Table {

    protected ?string $tableName = 'users_changes';

}
