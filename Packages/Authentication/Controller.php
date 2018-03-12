<?php

namespace Authentication;

use App;

class Controller extends App\Controller 
{
    public function login(){
        $username = $this->request->get('username');
        $password = $this->request->get('password');
        $jwt = $this->model->getToken($username, $password);
        $this->output->reply($jwt);
    }
}