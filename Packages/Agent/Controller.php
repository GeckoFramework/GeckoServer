<?php
namespace Agent;

use App;

class Controller extends App\Controller
{
    function list()
    {
        $name = $this->request->get('name');
        $user = $this->request->user();
        $agents = $this->model->get();
        $this->output->reply($agents);
    }
    function create()
    {
        if ($name = $this->request->get('name')) {
            $agents = $this->model->create([
                'name' => $name,
                'created_at' => date('Y-m-d H:i:s', time())
            ]);
            $this->output->reply($agents);
        } else {
            $this->output->reply('Parametro name mancante', $this->output->use()::CODE_MISSING_PARAMETER);
        }
    }
    function setName()
    {
        if ( ($newName = $this->request->get('name')) && ($id = (int)$this->request->get('id'))) {
            $agents = $this->model->updateName($newName, $id);
            $this->output->reply($agents);
        } else {
            $this->output->reply('Parametro name mancante', $this->output->use()::CODE_MISSING_PARAMETER);
        }
    }
}