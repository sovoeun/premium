<?php
session_start();
require_once __DIR__ . "/vendor/autoload.php";
use app\Server\Server as Server;
use app\Config\Constant as Constant;
$server= new Server();
$email="";$password="";
$result="";

if(filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_STRING)){
    $email=filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);
    $password=filter_input(INPUT_POST,'password',FILTER_SANITIZE_STRING);
    $result=$server->login($email,$password);
}elseif(isset($_SESSION['FBID'])){
    $name=isset($_SESSION['FULLNAME'])?$_SESSION['FULLNAME']:"";
    $socialId=isset($_SESSION['FBID'])?$_SESSION['FBID']:"";
    $email=isset($_SESSION['EMAIL'])?$_SESSION['EMAIL']:"";
    $result=$server->register($name,$email,"",$socialId,2);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Administrator</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="Style/css/theme.css"/>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4 panel panel-default">
            <div class="alert-warning spece-top">
                <?php
                if(filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_STRING) || isset($_SESSION['FBID'])) {
                    if (!empty($result) && is_array($result) && $result['code'] == 0) {
                        echo "<ul>";
                        foreach ($result['data'] as $key => $value) {
                            echo "<li>". $value . "</li>";
                        }
                        echo "</ul>";
                    } else{
                        if(isset($_SESSION['FBID'])){
                            if(isset($result['data']['email']) && !empty($result['data']['email'])){
                                $_SESSION['userId']=isset($result['data']['id'])?$result['data']['id']:"";
                                header("Location: " . Constant::BASEURL.'administrator/general.php');
                            }else{
                                header("Location: " . Constant::BASEURL.'profile.php');
                            }
                        }else{
                            $_SESSION['userId']=isset($result['data']['id'])?$result['data']['id']:"";
                            header("Location: " . Constant::BASEURL.'administrator/general.php');
                        }

                    }
                }
                ?>
            </div>

            <div class="account-wall">
                <form class="form-signin" method="post">
                    <div class="control-group">
                        <label class="control-label spece-top" for="inputEmail">Email</label>
                        <div class="controls">
                            <input type="email" class="form-control" placeholder="Email" required id="inputEmail" name="email" value="<?= $email ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label spece-top" for="inputPassword">Password</label>
                        <div class="controls">
                            <input type="password" class="form-control " placeholder="Password" name="password" required id="inputPassword" value="<?= $password?>"/>
                        </div>
                    </div>
                    <a href="register.php" class="pull-right">Register Now</a><br/>
                    <a href="app\Libraries\fbconfig.php" class="pull-right">Login with Facebook</a>
                    <input type="submit" class="btn btn-lg btn-primary btn-block spece-top" name="submit" value="Sign in"><br/>
                </form>
            </div>
            <p class="text-center text-uppercase">COPYRIGHT &copy 2016  Voeun So</p>
        </div>
    </div>
</div>
</body>
</html>

