<?php

class MITRABUKALAPAK{
	public function __construct($Identity,$refresh_token){
		$this->_Identity = $Identity;
		$this->_refresh_token = is_file("bl_refresh_token_$Identity") ? file_get_contents("bl_refresh_token_$Identity") : $refresh_token; 
		$this->_access_token = is_file("bl_access_token_$Identity") ? file_get_contents("bl_access_token_$Identity") : false;
	}

	private function request($method){
		if($method === true){
			$refresh_token = $this->_refresh_token;
			$Identity = $this->_Identity;

			$url = 'https://mitra.bukalapak.com/oauth/token';
			$postData = "{\"grant_type\":\"refresh_token\",\"refresh_token\":\"{$refresh_token}\"}";
		}else{
			$access_token = (!$this->_access_token) ? $this->generateToken() : $this->_access_token;

			$url = 'https://api.bukalapak.com/aggregate?access_token='.$access_token;
			$postData = "{\"aggregate\":{\"wallet\":{\"method\":\"GET\",\"path\":\"/mitra-payment/transactions?v=2&list_type=wallet\"},\"dana\":{\"method\":\"GET\",\"path\":\"/mitra-payment/transactions?v=2&list_type=dana\"},\"credits\":{\"method\":\"GET\",\"path\":\"/mitra-payment/transactions?v=2&list_type=credits\"}}}";
		}


		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = (isset($Identity)) ? $headers[] = 'Identity: '.$Identity : $headers[] = 'Accept: application/vnd.bukalapak.v4+json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = json_decode(curl_exec($ch),true);
		curl_close($ch);
		return $result;
	}

	private function generateToken(){
		$result = $this->request(true);

		if(isset($result['error'])) return false; //throw new Exception($result['error']['message']);
		

		if(!is_null(@$result['refresh_token'])) file_put_contents("bl_refresh_token_".$this->_Identity, $result['refresh_token']);
		if(!is_null(@$result['access_token'])) file_put_contents("bl_access_token_".$this->_Identity, $result['access_token']);


		return $result['access_token'];
	}

	private function getData(){
		retry:
		$result = $this->request(false);
		if(isset($result['errors'])){
			unlink("bl_access_token_".$this->_Identity);
			$this->generateToken();
			goto retry;
		}
		return $result;
	}

	public function mutasiAll(){
		$data = $this->getData();
		return $data;
	}
	public function mutasiCredits(){
		$data = $this->getData();
		return $data['data']['credit'];
	}
	public function mutasiWallet(){
		$data = $this->getData();
		return $data['data']['wallet'];
	}
	public function mutasiDana(){
		$data = $this->getData();
		return $data['data']['dana'];
	}
}
