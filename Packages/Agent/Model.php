<?php
namespace Agent;

use App;

class Model extends App\Model
{
    const table = "agents";
    public function updateName($name, $id)
    {
        if (is_string($name) && is_int($id)) {
            $query = 'UPDATE ' . static::table . ' SET  name=:name WHERE agentId=:id';
            return $this->database->query($query, [
                'name' => $name,
                'id' => $id,
            ]);
        }
    }
}