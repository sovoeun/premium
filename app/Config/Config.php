<?php
namespace app\Config;
use app\Config\Constant as Constant;
class Config
{

    function connection() {
        $con = mysqli_connect(Constant::SERVER, Constant::USERNAME, Constant::PASSWORD,Constant::DATABASENAME) or die(mysql_error());
        return $con;
    }

}