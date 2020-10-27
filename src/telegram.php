<?php

namespace sercanarga; class Telegram {
    public $api_url, $api_key;

	function __construct($settings) {
		$this->api_url = $settings['api_url'];
		$this->api_key = $settings['api_key'];
	}

	function request($method, $param) {
		$params = http_build_query($param);
		$url = "$this->api_url$this->api_key/$method?$params";
        return file_get_contents($url);
	}

	function save_img($url) {
        $dir = 'imgs/';
        $path = $dir.basename($url);
        file_put_contents($path, file_get_contents($url));
        return $path;
    }
}

?>
