<?php
class MITRABUKALAPAK{
	public function __construct($Identity,$refresh_token){
		$this->_Identity = $Identity;
		$this->_refresh_token = is_file("bl_refresh_token_$Identity") ? file_get_contents("bl_refresh_token_$Identity") : $refresh_token; 
		$this->_access_token = is_file("bl_access_token_$Identity") ? file_get_contents("bl_access_token_$Identity") : false;
	}

	private function generateToken(){
		$refresh_token = $this->_refresh_token;
		$Identity = $this->_Identity;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://mitra.bukalapak.com/oauth/token');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"grant_type\":\"refresh_token\",\"refresh_token\":\"{$refresh_token}\"}");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Identity: '.$Identity;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = json_decode(curl_exec($ch),true);
		curl_close($ch);
		if(isset($result['error'])) return false; //throw new Exception($result['error']['message']);
		

		if(!is_null(@$result['refresh_token'])) file_put_contents('./'."bl_refresh_token_$Identity", $result['refresh_token']);
		if(!is_null(@$result['access_token'])) file_put_contents('./'."bl_access_token_$Identity", $result['access_token']);


		return $result['access_token'];
	}

	private function getData(){
		$access_token = (!$this->_access_token) ? $this->generateToken() : $this->_access_token;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://api.bukalapak.com/aggregate?access_token='.$access_token);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"aggregate\":{\"wallet\":{\"method\":\"GET\",\"path\":\"/mitra-payment/transactions?v=2&list_type=wallet\"},\"dana\":{\"method\":\"GET\",\"path\":\"/mitra-payment/transactions?v=2&list_type=dana\"},\"credits\":{\"method\":\"GET\",\"path\":\"/mitra-payment/transactions?v=2&list_type=credits\"}}}");

		$headers = array();
		$headers[] = 'Accept: application/vnd.bukalapak.v4+json';
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = json_decode(curl_exec($ch),true);
		curl_close($ch);

		return $result;
	}

	public function mutasi(){
		$data = $this->getData();
		return $data;
	}
}
