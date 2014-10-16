<?php
namespace Pinvoice\Pipedrive;

// Require Composer packages
require_once ('vendor/autoload.php');

class API {

	/**
	 * @var object $_instance The current API instance.
	 * @var string $token     The Pipedrive API token.
	 * @var array  $classes   The available API classes (configurable).
	 * @var const  ENDPOINT   The API endpoint to use (configurable).
	 */
	private static $_instance = null;

	private static $token = null;

	private static $classes = array('Pipelines', 'Stages', 'Deals');

	const ENDPOINT = 'http://api.pipedrive.com/v1/';

	/**
	 * Returns singleton class instance.
	 *
	 * @return object Pipedrive API instance.
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Function used to allow dynamic methods like '$instance_of_API->getPipelines()'.
	 * Only uses registered classes in self::$classes array (!).
	 *
	 * @param string $name Name of method to be called.
	 * @param array  $args Arguments to calll method with.
	 *
	 * @throws Exception if method does not exist.
	 *
	 * @return mixed Returns method if method exists.
	 */
	public function __call($name, $arguments) {
		foreach (self::$classes as $class) {
			if (method_exists(__NAMESPACE__ . '\\' . $class, $name)) {
				return call_user_func_array(__NAMESPACE__ . '\\' . $class . '::' . $name, $arguments);
			}
		}
		throw new \Exception("Method '" . $name . "' does not exist.");
	}

	/**
	 * Gets the API token.
	 *
	 * @return string API token
	 */
	public static function getToken() {
		return self::$token;
	}

	/**
	 * Sets the API token.
	 *
	 * @param string $token Pipedrive API token.
	 *
	 * @return void
	 */
	public static function setToken($token) {
		self::$token = $token;
	}

	/**
	 * Handles errors in Pipedrive API requests.
	 *
	 * @param array $data JSON object.
	 *
	 * @return mixed Return data.
	 */
	public static function safe_return($data) {
		if (!$data->success) {
			throw new \Exception(isset($data->error) ? $data->error : "Unknown error");
		} else {
			return $data->data;
		}
	}

	/**
	 * HTTP GET wrapper for Curl.
	 * For requests without additional query parameters.
	 *
	 * @param string $url URL to GET request to.
	 *
	 * @return mixed Response data.
	 */
	public static function http_get($url) {
		$curl = new \Curl\Curl();
		$curl->get(self::ENDPOINT . $url . '?api_token=' . self::getToken());
		$curl->close();

		return json_decode($curl->response);
	}

	/**
	 * HTTP GET wrapper for Curl.
	 * For requests with additional query paramters.
	 *
	 * @param string $url URL to GET request to.
	 *
	 * @return mixed Response data.
	 */
	public static function http_get_with_params($url) {
		$curl = new \Curl\Curl();
		$curl->get(self::ENDPOINT . $url . '&api_token=' . self::getToken());
		$curl->close();

		return json_decode($curl->response);
	}

	/**
	 * HTTP POST wrapper for Curl.
	 *
	 * @param string $url URL to POST request to.
	 * @param array $args POST data.
	 *
	 * @return mixed Response data.
	 */
	public static function http_post($url, array $args = array()) {
		$curl = new \Curl\Curl();
		$curl->post(self::ENDPOINT . $url . '?api_token=' . self::getToken(), $args);
		$curl->close();

		return json_decode($curl->response);
	}

	/**
	 * Function to build the query sting.
	 * Will check which keys from $args are in $accepted_params, and build a query string from the key/val pairs.
	 *
	 * @param array $args Array of parameters (key,value).
	 * @param array $accepted_params Accepted parameter keys.
	 *
	 * @throws Exception if param is not in accepted params.
	 *
	 * @return string Query string for HTTP request.
	 */
	public static function build_query_string($args, $accepted_params) {
		$query_string = "";
		$first = true;

		foreach ($args as $key => $val) {
			if (in_array($key, $accepted_params)) {
				if ($first) {
					$query_string = $query_string . $key . '=' . $val;
				} else {
					$query_string = $query_string . '&' . $key . '=' . $val;
				}
				$first = false;
			} else {
				throw new \Exception("Param '" . $key . "' does not exist in function " . debug_backtrace()[1]['function'] . ".");
			}
		}

		return $query_string;
	}

}
?>
