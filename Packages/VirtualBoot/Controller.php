<?php

namespace VirtualBoot;

use App;

class Controller extends App\Controller 
{
    public function install(){
        $package = $this->request->get('name');
        try {
            $this->model->install($package);
            $this->output->reply("Package " . $package . " installed");
        } catch (\Exception $e) {
            $this->output->reply($e->getMessage());
        }
    }
    public function uninstall(){
        $package = $this->request->get('name');
        try {
            $this->model->uninstall($package);
            $this->output->reply("Package " . $package . " uninstalled");
        } catch (\Exception $e) {
            $this->output->reply($e->getMessage());
        }
    }
    public function activate(){
        $package = $this->request->get('name');
        try {
            $this->model->activate($package);
            $this->output->reply("Package " . $package . " activated");
        } catch (\Exception $e) {
            $this->output->reply($e->getMessage());
        }
    }
    public function deactivate(){
        $package = $this->request->get('name');
        try {
            $this->model->deactivate($package);
            $this->output->reply("Package " . $package . " deactivated");
        } catch (\Exception $e) {
            $this->output->reply($e->getMessage());
        }
    }
}