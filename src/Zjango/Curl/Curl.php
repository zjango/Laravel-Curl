<?php namespace Zjango\Curl;

class Curl {

    // The HTTP authentication method(s) to use.

    const AUTH_BASIC = CURLAUTH_BASIC;
    const AUTH_DIGEST = CURLAUTH_DIGEST;
    const AUTH_GSSNEGOTIATE = CURLAUTH_GSSNEGOTIATE;
    const AUTH_NTLM = CURLAUTH_NTLM;
    const AUTH_ANY = CURLAUTH_ANY;
    const AUTH_ANYSAFE = CURLAUTH_ANYSAFE;

    const USER_AGENT = 'Laravel-Curl (+https://github.com/zjango/Laravel-Curl)';

    public $error = false;
    public $error_code = 0;
    public $error_message = null;

    public $curl_error = false;
    public $curl_error_code = 0;
    public $curl_error_message = null;

    public $http_error = false;
    public $http_status_code = 0;
    public $http_error_message = null;

    private $_cookies = array();
    private $_headers = array();
    public $request_headers = null;
    public $response_headers = null;

    public $info;
    public $body = null;

    public function __construct()
    {
        $this->curl = curl_init();
        $this->setUserAgent(self::USER_AGENT);
        $this->setopt(CURLINFO_HEADER_OUT, true);
        $this->setopt(CURLOPT_HEADER, true);
        $this->setopt(CURLOPT_RETURNTRANSFER, true);
    }

	public function buildUrl($url, array $query)
	{
		if (!empty($query)) {
			$queryString = http_build_query($query);
			if(strpos($url, '?')===false){
				$url .= '?' . $queryString;
			}else{
				$url .= '&' . $queryString;
			}
		}
		return $url;
	}

    public function setBasicAuthentication($username, $password)
    {
        $this->setHttpAuth(self::AUTH_BASIC);
        $this->setopt(CURLOPT_USERPWD, $username . ':' . $password);
        return $this;
    }

    protected function setHttpAuth($httpauth)
    {
        $this->setOpt(CURLOPT_HTTPAUTH, $httpauth);
        return $this;
    }

    public function setHeader($key, $value)
    {
        $this->_headers[$key] = $key . ': ' . $value;
        $this->setopt(CURLOPT_HTTPHEADER, array_values($this->_headers));
        return $this;
    }

    public function setUserAgent($user_agent)
    {
        $this->setopt(CURLOPT_USERAGENT, $user_agent);
        return $this;
    }

    public function setReferrer($referrer)
    {
        $this->setopt(CURLOPT_REFERER, $referrer);
        return $this;
    }

    public function setCookie($key, $value)
    {
        $this->_cookies[$key] = $value;
        $this->setopt(CURLOPT_COOKIE, http_build_query($this->_cookies, '', '; '));
        return $this;
    }

    public function setOpt($option, $value)
    {
        return curl_setopt($this->curl, $option, $value);
    }

    public function verbose($on = true)
    {
        $this->setopt(CURLOPT_VERBOSE, $on);
    }

    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    public function reset()
    {
        $this->close();
        $this->_cookies = array();
        $this->_headers = array();
        $this->error = false;
        $this->error_code = 0;
        $this->error_message = null;
        $this->curl_error = false;
        $this->curl_error_code = 0;
        $this->curl_error_message = null;
        $this->http_error = false;
        $this->http_status_code = 0;
        $this->http_error_message = null;
        $this->request_headers = null;
        $this->response_headers = null;
        $this->body = null;
        $this->init();
    }

    public function get($url, $data = array())
    {
        if (count($data) > 0) {
            $this->setopt(CURLOPT_URL, $url . '?' . http_build_query($data));
        } else {
            $this->setopt(CURLOPT_URL, $url);
        }
        $this->setopt(CURLOPT_HTTPGET, true);
        return $this->sendRequest();
    }

    public function post($url, $data = array())
    {
        $this->setopt(CURLOPT_URL, $url);
        $this->setopt(CURLOPT_POST, true);
       if (is_array($data) || is_object($data))
		{
			$data = http_build_query($data);
		}
        $this->setopt(CURLOPT_POSTFIELDS, $data);
        return $this->sendRequest();
    }

    public function put($url, $data = array(), $json = 0)
    {
        if ($json == 0) {
            $url .= '?' . http_build_query($data);
        } else {
            $this->setopt(CURLOPT_POST, true);

            if (is_array($data) || is_object($data)) {
                $data = http_build_query($data);
            }

            $this->setopt(CURLOPT_POSTFIELDS, $data);
        }

        $this->setopt(CURLOPT_URL, $url);
        $this->setopt(CURLOPT_CUSTOMREQUEST, 'PUT');
        return $this->sendRequest();
    }

    public function patch($url, $data = array())
    {
        $this->setopt(CURLOPT_URL, $url);
        $this->setopt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->setopt(CURLOPT_POSTFIELDS, $data);
        return $this->sendRequest();
    }

    public function delete($url, $data = array())
    {
        $this->setopt(CURLOPT_URL, $url . '?' . http_build_query($data));
        $this->setopt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        return $this->sendRequest();
    }

    public function sendRequest()
    {
        $this->body = curl_exec($this->curl);
        $this->curl_error_code = curl_errno($this->curl);
        $this->curl_error_message = curl_error($this->curl);
        $this->curl_error = !($this->curl_error_code === 0);
        $this->info = curl_getinfo($this->curl);
        $this->http_status_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $this->http_error = in_array(floor($this->http_status_code / 100), array(4, 5));
        $this->request_headers = preg_split('/\r\n/', curl_getinfo($this->curl, CURLINFO_HEADER_OUT), null, PREG_SPLIT_NO_EMPTY);
        $this->response_headers = '';
        if (!(strpos($this->body, "\r\n\r\n") === false)) {
            list($response_header, $this->body) = explode("\r\n\r\n", $this->body, 2);
            while (strtolower(trim($response_header)) === 'http/1.1 100 continue') {
                list($response_header, $this->body) = explode("\r\n\r\n", $this->body, 2);
            }
            $this->response_headers = preg_split('/\r\n/', $response_header, null, PREG_SPLIT_NO_EMPTY);
        }

        $this->http_error_message = $this->error ? (isset($this->response_headers['0']) ? $this->response_headers['0'] : '') : '';

        $this->error = $this->curl_error || $this->http_error;
        $this->error_code = $this->error ? ($this->curl_error ? $this->curl_error_code : $this->http_status_code) : 0;
        $this->error_message = $this->curl_error ? $this->curl_error_message : $this->http_error_message;

        return $this;
    }

	public function toArray()
	{
		return array(
			'error'=>$this->error,
			'error_code'=>$this->error_code,
			'error_message'=>$this->error_message,
			'curl_error'=>$this->curl_error,
			'curl_error_code'=>$this->curl_error_code,
			'curl_error_message'=>$this->curl_error_message,
			'http_error'=>$this->http_error,
			'http_status_code'=>$this->http_status_code,
			'http_error_message'=>$this->http_error_message,
			'_cookies'=>$this->_cookies,
			'_headers'=>$this->_headers,
			'request_headers'=>$this->request_headers,
			'response_headers'=>$this->response_headers,
			'info'=>$this->info,
			'body'=>$this->body
		);
	}

	public function toJson()
	{
		return json_encode($this->toArray());
	}

	public function __toString()
	{
		return $this->body;
	}

	public function set()
	{
		$this->body='hihihi';
		return $this;
	}

    public function __destruct()
    {

        $this->close();
    }

}