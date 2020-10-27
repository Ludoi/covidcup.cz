<?php
declare(strict_types=1);

namespace App;

use \Nette\Database\Context;
use \Nette\Database\Table\ActiveRow;
use \Nette\Database\Table\Selection;
use \Nette\InvalidStateException;

/**
 * Base table repository.
 *
 * @author     Luděk Bednarz
 * @package    Covidcup
 */
abstract class Table 
{
    use \Nette\SmartObject;
    
    /** @var Context */
    protected Context $connection;

    /** @var string|null */
    protected ?string $tableName = null;

    protected array $cache = array();
    
    /**
     * @param Context $db

     */
    public function __construct(Context $db)
    {
        $this->connection = $db;
        
        if (is_null($this->tableName)) {
            $class = get_class($this);
            throw new InvalidStateException("Název tabulky musí být definován v $class::\$tableName.");
        }
    }
    
    public function getDatabase(): Context {
        return $this->connection;
    }

    /**
     * Vrací celou tabulku z databáze
     * @return Selection
     */
    protected function getTable(): Selection
    {
        return $this->connection->table($this->tableName);
    }

    /**
     * Vrací všechny záznamy z databáze
     * @return Selection
     */
    public function findAll(): Selection
    {
        return $this->getTable();
    }

    /**
     * Vrací vyfiltrované záznamy na základě vstupního pole
     * (pole array('name' => 'David') se převede na část SQL dotazu WHERE name = 'David')
     * @param array $by
     * @return Selection
     */
    public function findBy(array $by): Selection
    {
       return $this->getTable()->where($by);
    }

    /**
     * To samé jako findBy akorát vrací vždy jen jeden záznam
     * @param array $by
     * @return ActiveRow|null
     */
    public function findOneBy(array $by): ?ActiveRow
    {
        return $this->findBy($by)->limit(1)->fetch();
    }

    /**
     * Vrací záznam s daným primárním klíčem
     * @param int $id
     * @return ActiveRow|null
     */
    public function find($id): ?ActiveRow
    {
       if (!isset($this->cache[$id])) {
          $this->cache[$id] = $this->getTable()->get($id);
       }
       return $this->cache[$id];
    }

    public function countBy(array $by): int
    {
        return $this->getTable()->where($by)->count('*');
    }

    public function insert($data)
    {
       return $this->getTable()->insert($data);
    }

    public function update($data)
    {
       $this->getTable()->update($data);
    }
}