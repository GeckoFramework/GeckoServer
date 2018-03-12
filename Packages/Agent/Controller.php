<?php
namespace Agent;

use App;

class Controller extends App\Controller
{
    function list($request)
    {
        $user = $request->request->user();
        $agents = $this->model->get();
        $this->output->reply($agents);
    }
    function create($request)
    {
        if ($name = $request->get('name')) {
            $agents = $this->model->create([
                'name' => $name,
                'created_at' => date('Y-m-d H:i:s', time())
            ]);
            $this->output->reply($agents);
        } else {
            $this->output->reply('Parametro name mancante', $this->output->use()::CODE_MISSING_PARAMETER);
        }
    }
    function setName($request)
    {
        if ( ($newName = $request->get('name')) && ($id = (int)$request->get('id'))) {
            $agents = $this->model->updateName($newName, $id);
            $this->output->reply($agents);
        } else {
            $this->output->reply('Parametro name mancante', $this->output->use()::CODE_MISSING_PARAMETER);
        }
    }
}