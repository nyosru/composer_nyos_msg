<?php

/**
  класс модуля
 */

namespace Nyos;

//if (!defined('IN_NYOS_PROJECT'))
//    throw new \Exception('Сработала защита от розовых хакеров, обратитесь к администрратору');

class Msg {

    /**
     * smsaero.ru
     * @var type 
     */
    public static $system = ''; 

        public static $send_now = false;
    public static $admins_id = [];

    
    public static function enter(  ) {

        return false;
    }

    public static function send(  ) {

        return false;
    }

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
    public static function sendTelegramm(string $text, $to_id = null, $secret = null, $token = null) {
        // telegramm

        if ($to_id === null) {

            // если секрет = 2 то шлём тех оповещение админу сайта и мне
            if ($secret == 2) {

                // 93066902 - максим яподомик суши
                // 360209578 - я базовый
                // 860515561 - мой ак на буке

                if (!empty(self::$admins_id)) {
                    $go = array_unique(self::$admins_id);
                } else {
                    $go = [];
                }

                if ($_SERVER['HTTP_HOST'] == 'adomik.uralweb.info' ||
                        $_SERVER['HTTP_HOST'] == 'adomik.dev.uralweb.info' ||
                        $_SERVER['HTTP_HOST'] == 'photo.uralweb.info'
                ) {

                    // 93066902 - максим яподомик суши
                    $go[] = 93066902;

                    // 860515561 - мой ак на буке
                    $go[] = 860515561;
                }

                if (!empty($go))
                    foreach ($go as $tele_id) {

                        file_get_contents('https://api.uralweb.info/telegram.php?' . http_build_query([
                                    's' => md5($_SERVER['HTTP_HOST']),
                                    'id' => $tele_id,
                                    'token' => $token,
                                    'msg' => $text,
                                    'domain' => $_SERVER['HTTP_HOST']
                        ]));
                    }
            }
            // если секрет = 1 то шлём тех оповещение мне
//            else {
//                
//            }

            file_get_contents('https://api.uralweb.info/telegram.php?' . http_build_query([
                        's' => md5(1),
                        'msg' => $text,
                        'domain' => $_SERVER['HTTP_HOST']
            ]));
        } else {

            file_get_contents('https://api.uralweb.info/telegram.php?' . http_build_query(array(
                        's' => isset($secret{5}) ? $secret : md5($_SERVER['HTTP_HOST']),
                        'id' => $to_id,
                        'token' => $token,
                        'msg' => $text,
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
    public static function sendVkFromGroup($text, $to_id, $from = 'uralweb_info') {

        // echo __FUNCTION__;

        $e = file_get_contents('https://api.uralweb.info/vk.php?' . http_build_query(array(
                    's' => md5('send' . $from . $to_id),
                    'group' => $from,
                    'to_user' => $to_id,
                    'msg' => $text,
                    'domain' => $_SERVER['HTTP_HOST']
        )));

//            $e = json_decode($e);
//            echo '<pre>'; print_r($e); echo '</pre>';
    }


    
}

/**
* пример работы с классом SmsaeroApi
*/
//include_once('SmsaeroApiV2.class.php');
//use SmsaeroApiV2\SmsaeroApiV2;
//
//$smsaero_api = new SmsaeroApiV2('email', 'api_key', 'SIGN'); // api_key из личного кабинета
//var_dump($smsaero_api->send(['70000000000','70000000001'],'Тестовая отправка', 'DIRECT')); // Отправка сообщений
//var_dump($smsaero_api->check_send(123456)); // Проверка статуса SMS сообщения
//var_dump($smsaero_api->sms_list(null,'тест',3)); //Получение списка отправленных sms сообщений
//var_dump($smsaero_api->balance()); // Запрос баланса
//var_dump($smsaero_api->auth()); // Тестовый метод для проверки авторизации
//var_dump($smsaero_api->cards()); // Получение списка платёжных карт
//var_dump($smsaero_api->addbalance(100, 12345)); // Пополнение баланса
//var_dump($smsaero_api->tariffs()); // Запрос тарифа
//var_dump($smsaero_api->sign_add('new sign')); // Добавление подписи
//var_dump($smsaero_api->sign_list()); // Получить список подписей
//var_dump($smsaero_api->group_add('new_group_name')); //Добавление группы
//var_dump($smsaero_api->group_list()); // Получение списка групп
//var_dump($smsaero_api->group_delete(123)); // Удаление группы
//var_dump($smsaero_api->contact_add('70000000000', null, null, 'male', 'name', 'surname', null, 'param example')); // Добавление контакта
//var_dump($smsaero_api->contact_delete(123)); // Удаление контакта
//var_dump($smsaero_api->contact_list()); // Список контактов
//var_dump($smsaero_api->blacklist_add(123)); // Добавление в чёрный список
//var_dump($smsaero_api->blacklist_delete(123)); // Удаление из чёрного списка
//var_dump($smsaero_api->blacklist_list()); // Список контактов в черном списке
//var_dump($smsaero_api->hlr_check('70000000000')); // Создание запроса на проверку HLR
//var_dump($smsaero_api->hlr_status(474664)); // Получение статуса HLR
//var_dump($smsaero_api->number_operator('79136535500')); // Определение оператора
//var_dump($smsaero_api->viber_send('70000000000', null, 'Bonus', 'INFO','Тестовое сообщение')); // Отправка Viber-рассылок
//var_dump($smsaero_api->viber_statistic(1636)); // Статистика по Viber-рассылке
//var_dump($smsaero_api->viber_list());  // Список Viber-рассылок
//var_dump($smsaero_api->viber_sign_list()); // Список доступных подписей для Viber-рассылок