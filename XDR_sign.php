<?php
class SignRequests{
	/**
	 *
	 * Generates the url for the request
	 * @param string $HTTP_method the HTTP method to be used when generating the signature, i.e. GET, POST, etc.
	 * @param string $request_path the path to the resource to use for the call, i.e. "/v2/players"
	 * @param string $api_key the API key for the account to use for the call. This key can be found in the developers tab in Backlot
	 * @param string $secret_key the Secret key for the account to use for the call. This key can be found in the developers tab in Backlot
         * @param string $expires UNIX timestamp (seconds since Jan 1st, 1970) indicating when your request should be valid until
	 * @param array $parameters extra parameters to be specified in the url, i.e include => metadata
	 * @param string $request_body string containing data in JSON format to update or create objects
         * @return string containing the URL for the API call to be made
	 */
	public function generateURL($HTTP_method, $api_key, $secret_key, $expires, $request_path, $request_body = "", $parameters=array())
	{

		$parameters["api_key"] = $api_key;
		$parameters["expires"] = $expires;
		$signature = $this->generateSignature($HTTP_method, $secret_key, $request_path, $parameters, $request_body);
		$base = "https://api.ooyala.com";
		$url = $base.$request_path."?";
		foreach ($parameters as $key => $value) {
			$url .=  $key . "=" . urlencode($value) . "&";
		}
		$url .= "signature=" . $signature;
		return $url;
	}

	/**
	 *
	 * Generates the signature for a request
	 * @param $secretKey secret key
	 * @param $HTTPMethod GET, POST, PUT, PATCH, DELETE
	 * @param $requestPath the path of the object to request
	 * @param $parameters array of parameters [key => value]
	 * @param $request_body body for the request
	 */
	public function generateSignature($HTTP_method, $secret_key, $request_path, $parameters, $request_body = "")
	{
		$to_sign = $secret_key . $HTTP_method . $request_path;
		$keys = $this->sortKeys($parameters);
		foreach ($keys as $key) {
			$to_sign .= $key . "=" . $parameters[$key];
		}
		$to_sign .= $request_body;
		$hash = hash("sha256", $to_sign,true);
		$base = base64_encode($hash);
		$base = substr($base,0,43);
		$base = urlencode($base);
		return $base;

	}

	private function sortKeys($array)
	{
		$keys = array();$ind=0;
		foreach ($array as $key => $val) {
			$keys[$ind++]=$key;
		}
		sort($keys);
		return $keys;
	}
}
?>