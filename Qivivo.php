<?php

class Qivivo {

	private $qivivoLogin = "me@email.com";
	private $qivivoPassword = "my_password";

	private $credentials = NULL;
	private $thermostatStatus = NULL;
	private $urlGetLogin = "http://www.qivivo.com/login";
	private $urlLogin = "http://www.qivivo.com/login_check";
	private $urlThermostatStatus = "http://www.qivivo.com/myQivivo/updateRealTime";
	private $urlThermostatSetTemperature = "http://www.qivivo.com/myQivivo/temps-reel/setTemperature";
	private $urlAccount = "http://www.qivivo.com/mon-compte/mes-produits";
	private $dailyUsage = "http://www.qivivo.com/myQivivo/synthese/day";
	private $singlePageAuth = false;
	private $enableLog = false;
	
	function __construct($login, $password) {
		$this->qivivoLogin = $login;
		$this->qivivoPassword = $password;

		if ($this->qivivoLogin == "") {
			$this->error(703, "No username param");
		}

		if ($this->qivivoPassword == "") {
			$this->error(704, "No password param");
		}
	}

	function __destruct() {
		
	}

	private function error($code, $message) {
		die("[".$code."] ".$message.PHP_EOL);
	}

	private function log($log, $level) {
		if ($level < 5 && $this->enableLog) {
			echo $log.PHP_EOL;
		}
	}

	function getAccountInfo() {
		$this->connect();
		$ch = curl_init($this->urlAccount);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: ".$this->buildCookie(), "Host: www.qivivo.com"));
		curl_setopt($ch, CURLOPT_REFERER, "http://www.qivivo.com/myQivivo/");
		$result = curl_exec($ch);
		
		preg_match_all("/(<li>Niveau des piles : )(.*)(%<\/li>)/m", $result, $battery);
		
		$infos = new StdClass();

		if (count($battery) == 4) {
			if ($battery[2][0] != "") {
				$infos->battery = intval($battery[2][0]);
			}
		}

		preg_match_all("/(<li>Numéro de série : )(.*)(<\/li>)/m", $result, $serial);
		
		if (count($serial) == 4) {
			$infos->serial = $serial[2][0];
		}

		preg_match_all("/(<li>Version Firmware : )(.*)(<\/li>)/m", $result, $firmware);
		
		if (count($firmware) == 4) {
			$infos->firmware = $firmware[2][0];
		}

		preg_match_all("/(<li>Dernière transmission : )(.*)(<\/li>)/m", $result, $lastTransmission);
		
		if (count($lastTransmission) == 4) {
			$infos->lastTransmission = $lastTransmission[2][0];
		}

		return $infos;
		
	}

	private function connect() {
		if ($this->credentials == NULL) {
			$this->getCredentials();
		}

		if ($this->thermostatStatus == NULL) {
			$this->getThermostatInfo();
		}
	}

	private function getCredentials() {
		
		$this->log("Getting credentials", 4);

		$bCookie = "qivivo=abc";
		if (!$this->singlePageAuth) {
			$ch = curl_init($this->urlGetLogin);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			$result = curl_exec($ch);
			preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $baseCookie);
			$bCookie = "";
			if (count($baseCookie) > 1) {
				for ($i = 1 ; $i < count($baseCookie) ; $i++) {
					$bCookie .= $baseCookie[$i][0].";";
				}
			}
			$bCookie = rtrim($bCookie, ";");
		}
		
		// Retrieve csrf token
		preg_match_all("/( name=\"_csrf_token\" value=\")(.*)(\")/m", $result, $csrfData);

		if (count($csrfData) == 4) {
			if ($csrfData[2][0] != "") {
				$csrf = $csrfData[2][0];
			}
		}

		$this->log("Qivivo csrf token : ".$csrf, 3);


		$ch = curl_init($this->urlLogin);
		$fields = array(
			'_csrf_token' => urlencode($csrf),
			'_username' => urlencode($this->qivivoLogin),
			'_password' => urlencode($this->qivivoPassword)
		);

		$fields_string = "";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		$fields_string = rtrim($fields_string, '&');

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Origin: http://www.qivivo.com', 
			'Referer: http://www.qivivo.com/login', 
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8', 
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36', 
			'Upgrade-Insecure-Requests: 1',
			'Accept-Encoding: gzip, deflate',
			'Accept-Language: fr,en-US;q=0.8,en;q=0.6,fr-FR;q=0.4',
			'Cookie: '.$bCookie
		));
		//'Cookie: compatEnergy=gaz; compatTHMarque=delta-dore; cookieCompatibility=true; __utma=; __utmc=; __utmz=utmccn=utmcmd=referral|utmcct=/; _gat=1; PHPSESSID=; PRUM_EPISODES=s=&r=http%3A//www.qivivo.com/login'
		$this->log(str_replace(">", "]", str_replace("<", "[", @$result)), 5);
		// get headers too with this line
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

		$result = curl_exec($ch);
		// get cookie
		// multi-cookie variant contributed by @Combuster in comments
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
		// var_dump($result);die;

		$this->credentials = array();
		foreach($matches[1] as $item) {
		    parse_str($item, $cookie);
		    $this->credentials = array_merge($this->credentials, $cookie);
		}
		$this->log("Credentials retrieved : ".implode(" / ", $this->credentials), 5);
		if (count($this->credentials) == 0) {
			$this->error(701, "Could not load credentials", false);
		}
		$this->log(str_replace(">", "]", str_replace("<", "[", $result)), 5);
	}

	private function buildCookie() {
		$ret = "";
		foreach ($this->credentials as $key => $value) {
			$ret .= $key."=".$value.";";
		}
		$ret = rtrim($ret, ";");
		return $ret;
	}

	private function getThermostatInfo() {
		$this->log("Retrieve thermostat info", 4);
		$ch = curl_init($this->urlThermostatStatus);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: ".$this->buildCookie()));
		$result = curl_exec($ch);
		
		if (!$result) {
			$this->error(702, "Could not retrieve thermostat data");
		}
		$this->log(str_replace(">", "]", str_replace("<", "[", $result)), 5);
		$this->thermostatStatus = json_decode($result);
	}

	function getInsideTemperature() {
		$this->connect();
		$this->log("Retrieve thermostat inside value : ".@$this->thermostatStatus->getData->temperatureIn, 4);
		return @$this->thermostatStatus->getData->temperatureIn;
	}

	function getOutsideTemperature() {
		$this->connect();
		$this->log("Retrieve thermostat outside value : ".@$this->thermostatStatus->getData->temperatureOut, 4);
		return @$this->thermostatStatus->getData->temperatureOut;
	}

	function getRequestedTemperature() {
		$this->connect();
		$this->log("Retrieve thermostat requested value : ".@$this->thermostatStatus->getData->tcons, 4);
		return @$this->thermostatStatus->getData->tcons;
	}

	function getThermostatStatus() {
		$this->connect();
		$this->log("Thermostat status : ".@$this->thermostatStatus->getData->getAllData->qst_heating_state, 4);
		return $this->thermostatStatus->getData->getAllData->qst_heating_state;
	}

	function setTemperature($value) {
		$this->connect();
		$this->log("Trying to set temperature : ".$value, 1);
		$value = str_replace(",", ".", $value);
		if (!is_numeric($value)) {
			$this->error(705, "Param error in set temperature");
		}

		$this->log("Set temperature : ".$value, 1);
		$fields = array(
			't_ideal' => urlencode($value)
		);

		$fields_string = "";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		$fields_string = rtrim($fields_string, '&');

		$ch = curl_init($this->urlThermostatSetTemperature);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: ".$this->buildCookie()));
		$result = curl_exec($ch);
		$res = json_decode($result);
		if ($res->success->state != "success") {
			$this->error(706, "Could not set temperature");
		} else {
			$this->getThermostatInfo();
		}
		return $this->thermostatStatus->getData->getAllData->info_message;

	}

	function getMode() {
		$this->connect();
		$plannings = json_decode($this->thermostatStatus->getData->plannings);
		$currentPlanning = @$plannings->current_planning;
		$ret = "unknown";
		foreach (@$plannings->plannings as $planning) {
			if ($planning->id == $currentPlanning) {
				$ret = $planning->name;
				break;
			}
		}

		return $ret;
	}

}

?>