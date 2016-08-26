<?php
require_once __DIR__ . "/vendor/autoload.php";
use app\Server\Server as Server;
use app\Config\Constant as Constant;
$server= new Server();
$name="";$email="";$password="";
$result="";
if(filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_STRING)){
    $name=filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING);
    $email=filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);
    $password=filter_input(INPUT_POST,'password',FILTER_SANITIZE_STRING);
    $result=$server->register($name,$email,$password,"",1);
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
                if(filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_STRING)) {
                    if (!empty($result) && is_array($result) && $result['code'] == 0) {
                        foreach ($result['data'] as $key => $value) {
                            echo $value . "<br/>";
                        }
                    } else header("Location: " . Constant::BASEURL);
                }
                ?>
            </div>
            <div class="account-wall">
                <form class="form-singup" method="post">

                    <div class="control-group">
                        <label class="control-label spece-top" for="inputName">Name</label>
                        <div class="controls">
                            <input type="text" class="form-control" placeholder="Name" required id="inputName" name="name" value="<?= $name ?>"/>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label spece-top" for="inputEmail">Email</label>
                        <div class="controls">
                            <input type="email" class="form-control" placeholder="Email" required id="inputEmail" name="email" value="<?= $email ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label spece-top" for="inputPassword">Password</label>
                        <div class="controls">
                            <input type="password" class="form-control " placeholder="Password" required id="inputPassword" name="password" value="<?= $password ?>"/>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-lg btn-primary btn-block spece-top" name="submit" value="Register Now"/><br/><br/>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>