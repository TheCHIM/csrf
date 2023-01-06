<?php
class csrf{
//1. Считываем данные о текущем домене
//2. Проверяем если нет HTTP_REFERER, то генерируем новый токен
//3. Если есть HTTP_REFERER, но нет токена ни в POST, ни в  куках, ни в сессии, то посылаем в failedValidationToken()
//4. Если находим токен то, проверяем его и если он проходит validationToken() принимаем дынные формы и делаем
//   refreshToken() для новой страницы, если не проходит, то failedValidationToken()

    private static $use_script=true;//использоваться ли js скрипт(если false, то необходимо вставлять insertHiddenToken() )
    private static $url_script='http://stacionar/js/loadcsrf.js';//впишите сюда абсолютный путь к скрипту
    private static $lenght_token=20;
    private static $page = array();
    private static $token_name='csrf';
    private static $token_csrf='';
    private static $user_token_csrf='';
    private static $if_token_failure=1;//Способ ответа на неверный токен(0,1,2)
    private static $custom_message='Данные формы устарели!';
    private static $redirect_to_page='index.php';
    private static $cookie_token_domain='';
    private static $cookie_token_path='';
    private static $cookie_token_time=3600;//int
    private static $cookie_token_secure=false;//true если есть https    
    private static $cookie_token_httponly=false; //если true, то недоступны из JS
    private static $cookie_token_samesite='Lax'; //// None || Lax  || Strict
    
    public function __construct(){
        session_start();
        self::urlPage();
        self::validationToken();
        self::loadScript();       
    } 
    private function genToken() {
        //Генерация csrf токена
        self::$token_csrf = bin2hex(random_bytes(self::$lenght_token));        
    }
    private function urlPage() {
        //Заносим данные о текущей странице, домене и если есть, то о переходе со странице 
        $protocol= filter_input(INPUT_SERVER,'REQUEST_SCHEME', FILTER_SANITIZE_URL);
        if(isset($_SERVER['REMOTE_ADDR'])){
            $remote_ip=filter_input(INPUT_SERVER,'REMOTE_ADDR', FILTER_VALIDATE_IP);
            self::$page['ip']=$remote_ip;
        }
        else {
            self::$page['ip']='';
        }
        $domain=filter_input(INPUT_SERVER,'SERVER_NAME', FILTER_SANITIZE_URL);
        $url=$protocol.'://'.$domain;
        self::$page['url']=$url;
        if(isset($_SERVER['HTTP_REFERER']) && isset($_POST)){            
            $origin=filter_input(INPUT_SERVER,'HTTP_ORIGIN', FILTER_SANITIZE_URL);
            if($_SESSION['domain']==$url){
                self::$page['domain']=$url;                
            }
            else{
            self::$page['domain']=$origin;                                    
            $_SESSION['domain']=$url;//записывааем будущий origin
            }
        }                                  
    }
    private function getToken() {        
        if(!isset($_POST)){            
            self::refreshToken();
            return true;
        }
        elseif(isset($_GET)&&empty($_POST)){            
            self::refreshToken();
            return true;
        }
        elseif(!empty($_POST[self::$token_name])){
            self::$user_token_csrf=filter_input(INPUT_POST,self::$token_name, FILTER_SANITIZE_URL);
            return true;
        }
        elseif(isset($_COOKIE[self::$token_name])){
            self::$user_token_csrf=filter_input(INPUT_COOKIE,self::$token_name, FILTER_SANITIZE_URL);
            return true;
        }        
        else {
            return false;            
        }
    }     
    private function validationToken() {
        if(self::getToken()==true){
            if((!empty($_GET))or(empty($_GET)&&empty($_POST))){//ничего не делаем если запрос поступил из GET
            }
            elseif(!isset($_SERVER['HTTP_REFERER'])){//если просто первая страница или обновление
                self::refreshToken();               
            }
            elseif(isset($_SERVER['HTTP_REFERER'])){//если есть переход с другой страницы
                $full_token= self::$page['domain'].session_id().self::$page['ip'].self::$user_token_csrf;                
                if(($full_token!=$_SESSION['serv_token'])){//если токен не прошёл проверку                                  
                    self::failedValidationToken();
                }
                else{                     
                    self::refreshToken('no');
                }            
            }         
        } 
        else {
            self::failedValidationToken();            
        } 
    }
    private function refreshToken($gen_token=null) {        
        if($gen_token==null){//Если не нужно генерировать токен, то просто обновляем его время действия
            self::genToken();            
        }
        else{
            self::$token_csrf= self::$user_token_csrf;
        }
        //записываем сам токен
        $options=array(
            'expires' => time() + self::$cookie_token_time,
            'path' => self::$cookie_token_path,
            'domain' => self::$cookie_token_domain, 
            'secure' => self::$cookie_token_secure,     // true или false
            'httponly' => self::$cookie_token_httponly,    // true или false
            'samesite' => self::$cookie_token_samesite // None || Lax  || Strict
        );
        setcookie(
            self::$token_name, 
            self::$token_csrf,
            $options
            );
        setcookie('token_name',self::$token_name,time() + self::$cookie_token_time);//для js скрипта
        $_SESSION['serv_token']= self::$page['url'].session_id().self::$page['ip'].self::$token_csrf;
    }
    private function failedValidationToken() {        
        setcookie(self::$token_name, self::$token_csrf, time()-3600);    
        self::refreshToken();
        switch (self::$if_token_failure) {
            case 0:
                header('HTTP/1.0 403 Forbidden');
                exit("<h2>403 Доступ запрещён!</h2>");
            break;
            case 1://Своё сообщение                                
                exit("<h2>".self::$custom_message."</h2>");                
            break;
            case 2://переадресация на другую страницу
                header("Location:".self::$redirect_to_page);
            break;           
        }                
    }
    private function loadScript(){
        if(self::$use_script==true){
            echo '<script defer type="text/javascript" src="'.self::$url_script.'"></script>';
        }
    }       
    public function insertHiddenToken() {//используется если отключено использование js скрипта
        $user_token=filter_input(INPUT_COOKIE, self::$token_name,FILTER_SANITIZE_URL);
        echo '<input type="hidden" id="csrf" name="'.self::$token_name.'" value="'.$user_token.'">';
    }
    public function tokenName() {
        return self::$token_name;
    }
    public function token() {
        return self::$token_csrf;
    }    
}