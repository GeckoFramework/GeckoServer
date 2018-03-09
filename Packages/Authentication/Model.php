<?php
namespace Authentication;

use App;
use \Firebase\JWT\JWT;

App\Kernel::includePackageFile('Authentication', 'lib/JWT');

class Model extends App\Model
{
    public function getToken($username, $password)
    {
        $res = $this->Database->query("SELECT user_id FROM users WHERE username = :username AND password = :password", [
            "username" => $username,
            "password" => $password
        ]);
        if (count($res) > 0) {
            $token = array(
                "exp" => strtotime('+1 day'),
                "usr" => $res[0]['user_id']
            );
            return JWT::encode($token, App\Kernel::getConfig("JWT_PRIVATE_KEY"));
        } else {
            return false;
        }
    }
}