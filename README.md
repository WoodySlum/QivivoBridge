# QivivoBridge
PHP plugin to access Qivivo thermostat data. I needed to quickly support this thermostat for my Hautomation system.

Don't forget to star this repo if you like it !

* Get several thermostat informations 
* Set temperature

This plugin has been realized using HTTP reverse enginering. 

***Please note that Qivivo is working on public APIs, so this code will be soon obsolete.***

## Requirements

PHP 5.X and upper, with CURL module and valid Internet connection.

## Usage

Include the class using classic PHP commands :

`include_once(__DIR__ ."Qivivo.php");`

### Login and before all

Retrieve the thermostat instance

`$thermostat = new Qivivo("your@email.com", "your_password");`

### Account info

Get some Qivivo account info

	$aInfos = $thermostat->getAccountInfo();
	var_dump($aInfos);

*Output :*

	object(stdClass)#21 (4) {
	  ["battery"]=>
	  int(24)
	  ["serial"]=>
	  string(10) "XXXXXXXXXXXX"
	  ["firmware"]=>
	  string(2) "14"
	  ["lastTransmission"]=>
	  string(19) "18/02/2016 17:19:15"
	}
	
### Inside temperature

Get some in house temperature

	$inTemperature = $thermostat->getInsideTemperature();
	var_dump($inTemperature);

*Output :*

	string(4) "20.5"

### Outside temperature

Get some outside temperature

	$outTemperature = $thermostat->getOutsideTemperature();
	var_dump($outTemperature);

*Output :*

	int(8)

### Thermostat requested temperature

Get the requested thermostat temperature

	$reqTemperature = $thermostat->getRequestedTemperature();
	var_dump($reqTemperature);

*Output :*

	string(4) "21.0"

### Thermostat status

Get the thermostat status

	$status = $thermostat->getThermostatStatus();
	var_dump($status);

*Output :*

	string(2) "ON"


### Set the temperature

Set the temperature to whatever you want. This will send back a message from Qivivo.

	$message = $thermostat->setTemperature(19.5);
	var_dump($message);

*Output :*

	string(53) "Votre demande sera prise en compte dans 5 min environ"

### Get the mode

Set the current thermostat mode

	$mode = $thermostat->getMode();
	var_dump($mode);

*Output :*

	string(5) "smart"


### Output logs

Set the class var `$enableLog` to `true`

## License

Copyright (c) 2016, Sebastien MIZRAHI
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.











