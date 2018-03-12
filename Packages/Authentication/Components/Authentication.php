<?php
namespace Authentication\Components;

use App;
use \Firebase\JWT\JWT;

App\Kernel::includePackageFile('Authentication', 'lib/JWT');

class Authentication implements App\Interfaces\Request
{
    private static $user;

    function __construct()
    {
        App\Kernel::implementComponents($this, 'App\Interfaces\Output');
        App\Kernel::implementComponents($this, 'App\Interfaces\Database');
        App\Kernel::implementComponents($this, 'App\Interfaces\Request');
    }
    public function checkToken()
    {
        try {
            $token = $this->request->header("Authentication");
            $decoded = JWT::decode($token, App\Kernel::getConfig("JWT_PRIVATE_KEY"), array('HS256'));
            self::$user = $decoded->usr;
            return true;
        } catch (\Exception $e) {
            $this->output->reply("Not authorized");
            die();
        }
    }

    public function user()
    {
        return self::$user;
    }
}