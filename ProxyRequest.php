<?php
class ProxyRequest
{
    const DEFAULT_TIMEOUT_SECONDS = 10;
    const DEFAULT_RETRY_COUNT     = 2;

    private $_con     = null;
    private $_url     = null;
    private $_proxy   = null;
    private $_port    = null;
    private $_options = null;

    public function set_request_info($url = null, $proxy = null, $port = null, $options = array())
    {
        if (empty($url))
        {
            throw new Exception("An exception is thrown. Url is empty.");
        }
        
        if (empty($proxy))
        {
            throw new Exception("An exception is thrown. Proxy is empty.");
        }

        if (empty($port))
        {
            throw new Exception("An exception is thrown. Port is empty.");
        }

        if (!array_key_exists('timeout', $options))
        {
            $options['timeout'] = self::DEFAULT_TIMEOUT_SECONDS;
        }

        if (!array_key_exists('retry', $options))
        {
            $options['retry'] = self::DEFAULT_RETRY_COUNT;
        }

        $this->_url   = $url;
        $this->_proxy = $proxy;
        $this->_port  = $port;
        $this->_options = $options;
    }

    public function send_request()
    {
        if (empty($_con))
        {
            $this->_con = curl_init(); 
        }
        curl_setopt($this->_con, CURLOPT_URL, $this->_url);
        curl_setopt($this->_con, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($this->_con, CURLOPT_PROXY, $this->_proxy);
        curl_setopt($this->_con, CURLOPT_PROXYPORT, $this->_port);
        curl_setopt($this->_con, CURLOPT_TIMEOUT, $this->_options['timeout']);
        curl_setopt($this->_con, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($this->_con);
        echo $this->_url . "\n";

        $retry_count = 0;
        while (empty($response))
        {
            if ($retry_count == $this->_options['retry'])
            {
                break;
            }
            $retry_count++;
            echo "retry count " . $retry_count . "\n";
            $response = curl_exec($this->_con);
        }

        if (empty($response))
        {
            return false;
        }

        $header = curl_getinfo($this->_con);
        $response_data = $this->_adjust_format($header, $response);
        return $response_data;
    }

    public function close_request()
    {
        curl_close($this->_con);
        $this->_con = null;
    }

    private function _adjust_format($header, $response)
    {
        $body   = $response;
        $datas = array(
            'header'   => $header,
            'body'     => $body,
        );
        if (strpos($body, 'charset='))
        {
            preg_match("/charset=.+\"/", $body, $matches);
            $encoding = $matches[0];
            $vowels = array("'", '"');
            $encoding = str_replace($vowels, '', $encoding);
            $encoding = substr($encoding, strlen('charset='));
            $datas['encoding'] = $encoding;
            return $datas;
        }
        else
        {
            $datas['encoding'] = null;
            return $datas;
        }
    }
}
