<?php
namespace app\Server;
use app\Config\Config as Config;
use app\Config\Constant;

class Server
{
    public function __construct()
    {
        $this->config=new Config();
    }

    public function register($name,$email="",$password="",$fbId="",$type=1)
    {
        //type 1 account and 2 social
        try{
            if($type==1){
                $validationUser=$this->validateUser($name,$email,$password,1);
                if(!empty($validationUser)) return $this->messageError($validationUser);
                $checkUserExists=$this->checkUserExist($email);
                if($checkUserExists) return $this->messageError(array("Email Already Exists"));
                $password=$this->generatePassword($password);
            }else{
                $checkValidationSocial=$this->validationUserSocial($fbId,$name);
                if(!empty($checkValidationSocial)) return $this->messageSuccess($checkValidationSocial);
                $checkFBIDExists=$this->getUserBySocialId($fbId);
                if(!empty($checkFBIDExists)) return $this->messageSuccess($checkFBIDExists);
            }


            $result=mysqli_query($this->config->connection(),"INSERT INTO users(name,email,password,fb_id,type) 
            VALUES('$name','$email','$password','$fbId',$type)");
            if($result)
            {
                $getLastUser= mysqli_query($this->config->connection(),"SELECT * FROM users ORDER BY id DESC LIMIT 1;");
                $getLastUser=mysqli_fetch_array($getLastUser);
                return $this->messageSuccess($getLastUser);
            } else {
                return $this->messageError(array("Enable Register !"));
            }
        }catch (\Exception $e){
            return $this->messageError(array("Server Error !"));
        }

    }

    public function updateProfile($socialId, $email)
    {
        try{
            if(empty($socialId) || $socialId==false || $socialId==null) return $this->messageError(array("Invalid SocialID"));
            if(empty($email) || $email==false || $email=='') return $this->messageError(array("Please enter email first before continue"));
            $checkUserBySocialId=$this->getUserBySocialId($socialId);
            if(empty($checkUserBySocialId)) return $this->messageError(array("SocialId does not exists"));
            $result = mysqli_query($this->config->connection(),"UPDATE users set email='$email' WHERE fb_id='$socialId'");
            if($result)
            {
                return $this->messageSuccess($checkUserBySocialId);
            }else{
                return $this->messageError(array("Update Profile Fail"));
            }


        }catch(\Exception $e){
            return $this->messageError(array("Server Error !"));
        }
    }

    public function login($email,$password)
    {
        try{
            $validationUser=$this->validateUser("",$email,$password,0);
            if(!empty($validationUser)) return $this->messageError($validationUser);
            $password=$this->generatePassword($password);
            $email=htmlspecialchars($email,ENT_QUOTES,'UTF-8');
            $result = mysqli_query($this->config->connection(),"SELECT * FROM users WHERE email='$email' and password='$password'");
            $result=mysqli_fetch_array($result);
            if($result)
            {
                return $this->messageSuccess($result);
            }else{
                return $this->messageError(array("Login Fail"));
            }
        }catch (\Exception $e){
            return $this->messageError(array("Server Error !"));
        }
    }

    public function logout()
    {
        unset($_SESSION['userId']);
        unset($_SESSION['FBID']);
        unset($_SESSION['FULLNAME']);
        unset($_SESSION['EMAIL']);
        unset($_SESSION['USERNAME']);

    }

    public function saveFile($userId,$file)
    {
        try{
            if(empty($userId) || $userId==null || $userId=="") return $this->messageError(array("Please use UserId"));
            if(empty($file)) return $this->messageError(array("Please Select File First"));
            $uploadFile=$this->uploadFile($file);
            if (!empty($uploadFile) && is_array($uploadFile) && $uploadFile['code'] == 0) return $uploadFile;
            $fileName=explode("/",$uploadFile);
            $fileName = end($fileName);
            $getUser=$this->getUserById($userId);
            if (!empty($getUser) && is_array($getUser) && $getUser['code'] == 0) return $getUser;

            $path ="../Upload/".$fileName;
            $fromMail = "3b.group@angkorebuy.com";
            $subject = "This is a mail with attachment.";
            $to=$getUser['data']['email'];
            $msg = "Upload File.";
            $this->sendMail($to, $path , $fromMail, $subject, $msg);
            $result=mysqli_query($this->config->connection(),"INSERT INTO files(user_id,file) VALUES('$userId','$uploadFile')");
            if($result) return $this->messageSuccess(array("Upload File has been complete."));
            else return $this->messageSuccess(array("Unable Upload file Please try again latter."));
        }catch (\Exception $e){
            return $this->messageError(array("Server Error !"));
        }
    }

    private function uploadFile($file)
    {
        try{
            if(!empty($file)){
                $ext=array("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",'application/vnd.ms-excel','text/xls','text/xlsx');
                if($file['size']>1024000) return $this->messageError(array("Allow file only 1M"));
                if(!in_array($file['type'],$ext)) return $this->messageError(array("Allow Upload only Excel file."));
                $fileName=explode(".",$file['name']);
                $extension=end($fileName);
                $fileName=$fileName[0].rand(10,100).rand(10,100).rand(10,100).rand(10,100).rand(10,100);
                $fileName=$fileName.'.'.$extension;
                $fileName=str_replace(" ","",$fileName);
                $basePath="../Upload/".$fileName;
                $upload=move_uploaded_file($file['tmp_name'], $basePath);
                if($upload){
                    $fullPath=Constant::BASEURL.'Upload/'.$fileName;
                    return $fullPath;
                }
            }else {
                return $this->messageError(array("Please Select File First"));
            }
            return $this->messageError(array("Enable upload file please try again letter."));
        }catch (\Exception $e){
            return $this->messageError(array("Server Error !"));
        }
    }



    function sendMail($to, $path, $fromMail, $subject, $msg) {
        $headers = "From: $fromMail\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .="Content-Type: multipart/mixed; boundary=\"1a2a3a\"";

        $msg .= "If you can see this MIME than your client doesn't accept MIME types!\r\n"
            ."--1a2a3a\r\n";

        $msg .= "Content-Type: text/html; charset=\"iso-8859-1\"\r\n"
            ."Content-Transfer-Encoding: 7bit\r\n\r\n"
            ."Hi,How are you?Below your file has been upload to our website.\r\n"
            ."--1a2a3a\r\n";

        $file = file_get_contents($path);

        $msg .= "Content-Type: image/jpg; name=\"$path\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."Content-disposition: attachment; file=\"$path\"\r\n"
            ."\r\n"
            .chunk_split(base64_encode($file))
            ."--1a2a3a--";
        mail($to,$subject,$msg,$headers);
    }


    private function getUserById($userId)
    {
        $result = mysqli_query($this->config->connection(),"SELECT * FROM users WHERE id=$userId;");
        $result=mysqli_fetch_array($result);
        if($result)
        {
            return $this->messageSuccess($result);
        }else{
            return $this->messageError(array("Login Fail"));
        }
    }

    private function getUserBySocialId($socialId)
    {
        $result = mysqli_query($this->config->connection(),"SELECT * FROM users WHERE fb_id='$socialId';");
        $result=mysqli_fetch_array($result);
        return !empty($result[0])?$result:array();
    }

    private function validationUserSocial($socialId,$name)
    {
        $output=[];
        if(empty($socialId) || $socialId==false || $socialId==null || $socialId==""){
            $output[]="Invalid SocialID";
        }
        if(empty($name) || $name==false || $name=="" || $name==null){
            $output[]="Name could not be blank";
        }
        return $output;
    }

    private function messageError($data=[])
    {
        $output = array(
            "code" => 0,
            "data" => $data
        );
        return $output;
    }

    private function messageSuccess($data=[])
    {
        $output = array(
            "code" => 1,
            "data" => $data
        );
        return $output;
    }

    private function checkUserExist($email)
    {
        $result = mysqli_query($this->config->connection(),"SELECT * FROM users WHERE email='$email'") or die(mysql_error());
        $result=mysqli_fetch_array($result);
        if(empty($result) || $result==null || $result==false) return false; else return $result;
    }

    public function validateUser($name,$email,$password,$status=1)
    {
        $output=[];
        if($status==1 && (empty($name) || $name==null ||$name==''))
        {
            $output[]="Please Enter name first before continue";
        }

        if(empty($email) || $email==false || $email=='')
        {
            $output[]="Please enter email first before continue";
        }

        if(empty($email) || $email==false || $email=='' || strlen($password)<6)
        {
            $output[]="Please enter password and password must bigger then 6 char first before continue";
        }

        return $output;

    }

    public function generatePassword($password)
    {
        return base64_encode(sha1($password));
    }

}