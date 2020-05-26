<?php
    require 'vendor/autoload.php';
    use thiagoalessio\TesseractOCR\TesseractOCR;

    $url = '';
    $req = json_decode(file_get_contents('php://input'), true);
    $chat_id = $req['message']['chat']['id'];
    $message = $req['message'];

    require 'telegram.php';
    use sercanarga\Telegram;

    try {
        $api = new Telegram([
            'api_url' => 'https://api.telegram.org/bot',
            'api_key' => ''
        ]);

        //debug
//        $api->request('sendmessage', ['chat_id' => $chat_id, 'text' => json_encode($message, true)]);
        if (is_null($message['photo']) && is_null($message['document'])) {
            $api->request('sendmessage', ['chat_id' => $chat_id, 'text' => 'Yalnızca resim gönderin!']);
        } else {
            $file_type = (!is_null($message['photo'])) ? 'photo' : 'document';

            switch ($file_type) {
                case 'photo':
                        $file_id = ($message['photo'][1]['file_id'] != '') ? $message['photo'][1]['file_id'] : $message['photo'][0]['file_id'];
                    break;
                case 'document':
                        if (explode('/', $message['document']['mime_type'])[0] != 'image') {
                            $api->request('sendmessage', ['chat_id' => $chat_id, 'text' => 'Yalnızca resim gönderin!']);
                            exit;
                        }
                        $file_id = $message['document']['file_id'];
                    break;
            }

            $get_file = json_decode($api->request('getFile', ['file_id' => $file_id]), true)['result']['file_path'];
            $photo = "https://api.telegram.org/file/bot$api->api_key/$get_file";
            $img = $api->save_img($photo);
            $api->request('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $message['message_id'], 'revoke' => 1]);
            $scan = (new TesseractOCR($img))->run();
            $res = 'Burada IBAN yok kii... Göremiyorum, kör oldum. :)';
            if (preg_match('([a-zA-Z]{2}\d{2}\s?\d{4}\s?\d{4}\s?\d{4}\s?\d{4}\s?\d{4}\s?\d{2}|[a-zA-Z]{2}[0-9]{24})', $scan, $result)) {
		$res = $result[0];
            }
            $api->request('sendphoto', ['chat_id' => $chat_id, 'photo' => $url.$img, 'caption' => $res]);
			unlink($img);
        }

    } catch (Exception $e) {
        echo $e->getMessage();
    }
?>
