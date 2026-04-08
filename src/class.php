<?php

/**
  класс модуля
 */

namespace Nyos;

//if (!defined('IN_NYOS_PROJECT'))
//    throw new \Exception('Сработала защита от розовых хакеров, обратитесь к администрратору');

class Msg
{

    public static $domain = '';

    /**
     * smsaero.ru
     * @var type 
     */
    public static $system = '';
    public static $send_now = false;
    public static $admins_id = [];

    /**
     * система для смс - smsaero.ru
     * @var type 
     */
    public static $sms_system = '';
    public static $sms_class = null;
    public static $sms_login = '';
    public static $sms_pass = '';
    public static $sms_enter = false;
    public static $sms_podpis = '';
    // для настройки file get content
    // public static $domain_api_telega = 'https://api.uralweb.info';
    public static $domain_api_telega = 'https://api.php-cat.com';
    public static $domain_api = 'https://api.php-cat.com';

    public static function getDomain()
    {
        if (!empty(self::$domain))
            return self::$domain;

        // Проверяем наличие HTTP_HOST
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        // Для консольных команд
        return config('app.url') ?? 'example.com';
    }

    public static function enterSms()
    {

        if (!empty(self::$sms_system) && self::$sms_system == 'smsaero.ru' && !empty(self::$sms_login) && !empty(self::$sms_pass)) {
            require_once __DIR__ . '/../smsaero.ru/SmsaeroApiV2.class.php';
            self::$sms_class = new \SmsaeroApiV2(self::$sms_login, self::$sms_pass, 'SIGN'); // api_key из личного кабинета
            self::$sms_enter = true;
            return \f\end3('вошли в систему');
        }

        return \f\end3('не вошли в систему', false);
    }

    /**
     * отправка смс-ок
     * @param type $phones
     * @param type $text
     * @return boolean
     */
    public static function sendSms($phones, $text)
    {

        if (!empty(self::$sms_system) && self::$sms_system == 'smsaero.ru' && self::$sms_enter !== true)
            return \f\end3('входа нет', false);

        $list_phones = [];

        if (!empty($phones) && is_array($phones) && sizeof($phones) > 0) {

            foreach ($phones as $tel) {
                $list_phones[] = \f\gsm_rus($tel, 7);
            }
        }

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
    public static function sendTelegramm(string $text, $to_id = null, $secret = null, $token = null)
    {
        // telegramm

        if ($to_id === null) {

            // если секрет = 2 то шлём тех оповещение админу сайта и мне
            if ($secret == 2) {
                // 360209578 - я базовый
                if (!empty(self::$admins_id)) {
                    self::$admins_id[] = 360209578;
                    $go = array_unique(self::$admins_id);
                } else {
                    $go = [];
                    $go[] = 360209578;
                }

                if (!empty($go))
                    foreach ($go as $tele_id) {
                        file_get_contents(
                            self::$domain_api_telega . '/telegram.php?' . http_build_query([
                                's' => md5(self::getDomain()),
                                'id' => $tele_id,
                                'token' => $token,
                                'msg' => $text,
                                'domain' => self::getDomain()
                            ])
                        );
                    }
            } else {

                file_get_contents(self::$domain_api_telega . '/telegram.php?' . http_build_query([
                    's' => md5(1),
                    'msg' => $text,
                    'domain' => self::getDomain()
                ]));
            }
        } else {

            file_get_contents(self::$domain_api_telega . '/telegram.php?' . http_build_query([
                's' => isset($secret{5}) ? $secret : md5(self::getDomain()),
                'id' => $to_id,
                'token' => $token,
                'msg' => $text,
                'domain' => self::getDomain()
            ]));
        }
    }

    /**
     * отправка сообщения от группы - пользователю
     * @param string $text сообщение что шлём
     * @param integer $to_id кому шлём сообщение, или одна цифра или через запятую
     * @param string $from название группы от которой шлём сообщение
     * @param string $group_vk_token токен группы от которой шлём сообщение
     */
    public static function sendVkFromGroup($text, $to_id, $from = 'uralweb_info', $group_vk_token = null )
    {

        $url = self::$domain_api . '/api/vk/send?' . http_build_query(array(
            's' => md5('send' . $from . $to_id),
            'group' => $from,
            'to_user' => $to_id,
            'msg' => $text,
            'domain' => self::getDomain()
        ));

        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
            ],
        ]);

        $e = @file_get_contents($url, false, $context);

        if ($e === false) {
            $error = error_get_last();

            return json_encode([
                'status' => false,
                'message' => 'Ошибка отправки сообщения ВК',
                'error' => $error['message'] ?? 'unknown error',
            ], JSON_UNESCAPED_UNICODE);
        }

        json_decode($e);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $e;
        }

        return json_encode([
            'status' => true,
            'message' => 'Ответ получен не в JSON формате',
            'response' => $e,
        ], JSON_UNESCAPED_UNICODE);

        //            $e = json_decode($e);
        //            echo '<pre>'; print_r($e); echo '</pre>';
    }
}
