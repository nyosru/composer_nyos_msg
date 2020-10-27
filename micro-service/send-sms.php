<?php

//    use SmsaeroApiV2\SmsaeroApiV2;

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '0start.php';
    $skip_start = false;
}

try {

//\f\pa( [ \Nyos\Msg::$sms_system ,
//        \Nyos\Msg::$sms_login ,
//        \Nyos\Msg::$sms_pass ]
//        );

    \Nyos\Msg::enterSms();
    
    if( \Nyos\Msg::$sms_enter === true ){
        
        echo '<br/>'.__LINE__;
        
        \Nyos\Msg::smsSend( [ 89222622289 , 79998887766] , 'проверка' );
        
    }
    else{
        echo '<br/>'.__LINE__;
    }
    
//    $date = $in['date'] ?? $_REQUEST['date'] ?? null;
//
//    if (empty($date))
//        throw new \Exception('нет даты');
    // $sp = $_REQUEST['sp'] ?? $in['sp'] ?? null;
//    $sp1 = ceil( $_REQUEST['sp'] );
//
//    if (empty($sp1))
//        throw new \Exception('нет точки продаж');
//
//    if (isset($_REQUEST['show_info'])) 
//    \f\pa($_REQUEST,'','','request');
//    // echo '<h3>тащим всех работников в этом месяце</h3>';
//
//    
//    
//    $ee = \Nyos\mod\JobDesc::getActionsJobmansOnMonth( $db, $_REQUEST['jobmans'] , $_REQUEST['date'] );
//    // $ee = [];
//
//    if (isset($_REQUEST['show_info'])){
////    \f\pa($ee );
//    exit;
//    }

    \f\end2('ok', true, ( $result ?? [] ) );
    
} catch (Exception $exc) {

    if (isset($_REQUEST['show_info'])) {
        echo '<pre>';
        print_r($exc);
        echo '</pre>';
    }

    \f\end2('error', false, $exc);
    
}