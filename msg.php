<?php

namespace nyos;

class Msg {
    
    public static $send_now = false;

    /**
     * отправить сообщение в телеграмм
     * 
     * @param type $text
     * @param type $to_id 
     * @param type $secret
     * @param string $token
     * если null то админу сообщение
     * если id то шлём по адресу
     */
    public static function sendTelegramm( string $text, $to_id = null, $secret = null, $token = null ) {
        // telegramm

        if ($to_id === null) {

            file_get_contents('https://api.uralweb.info/telegram.php?' . http_build_query(array( 
                's' => md5($secret), 
                'msg' => $text, 
                'domain' => $_SERVER['HTTP_HOST'] 
                )));

        } else {

            file_get_contents('https://api.uralweb.info/telegram.php?' . http_build_query(array(
                's' => md5( $_SERVER['HTTP_HOST'] ) ,
                'id' => $to_id ,
                'token' => $token, 
                'msg' => $text , 
                'domain' => $_SERVER['HTTP_HOST']
            )));

        }
        
    }

    /**
     * отправка сообщения от группы пользователю
     * @param type $text
     * @param type $to_id
     * @param type $from
     */
    public static function sendVkFromGroup( $text, $to_id, $from = 'uralweb_info' ) {

        // echo __FUNCTION__;
        
            $e = file_get_contents('https://api.uralweb.info/vk.php?' . http_build_query(array(
                's' => md5('send'.$from.$to_id) ,
                'group' => $from ,
                'to_user' => $to_id ,
                'msg' => $text , 
                'domain' => $_SERVER['HTTP_HOST']
            )));

//            $e = json_decode($e);
//            echo '<pre>'; print_r($e); echo '</pre>';
            
    }

}
