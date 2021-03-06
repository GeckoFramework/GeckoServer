<?php
namespace PDO\Components;

use App;

class Component implements App\Interfaces\Database
{
    protected $db;

    function __construct()
    {
        try {
            $this->db = new \PDO(App\Kernel::getConfig('DATABASE_TYPE') . ':host=' . App\Kernel::getConfig('DATABASE_HOST') . ';port=' . App\Kernel::getConfig('DATABASE_PORT') . ';dbname=' . App\Kernel::getConfig('DATABASE_NAME'), App\Kernel::getConfig('DATABASE_USER'), App\Kernel::getConfig('DATABASE_PASSWORD'));
        } catch (\Exception $e) {
            var_dump($e);
            throw new \PDOException("Errore configurazione database", 4);
        }
        App\Kernel::implementComponents($this, 'App\Interfaces\Output');
    }
    function query($query, $parameters = array())
    {
        $query = $this->db->prepare($query);
        $query->execute($parameters);
        $error = $query->errorInfo();
        if ($error[0] !== "00000") {
            throw new PDOException($error[2], (int)$error[0]);
        }
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}

class PDOException extends \Exception
{
    function __construct($message, $code)
    {
        App\Kernel::implementComponents($this, 'App\Interfaces\Output');
        parent::__construct($message, $code);
    }
    public function __toString()
    {
        return $this->output->reply("Errore nella richiesta al database");
    }
}