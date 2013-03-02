<?php

/**
 * A Laravel 3.x package for working w/ Amazon Flexible Payments.
 *
 * @package    AmazonFPS
 * @author     Scott Travis <scott.w.travis@gmail.com>
 * @link       http://github.com/swt83/laravel-amazonfps
 * @license    MIT License
 */

class AmazonFPS {
    
    /**
     * Magic method for method calls.
     *
     * @param   string  $method
     * @param   args    $array
     * @return  array
     */
    public static function __callStatic($method, $args)
    {
        // set endpoint
        $endpoint = Config::get('amazonfps.production_mode') ? 'https://fps.amazonaws.com' : 'https://fps.sandbox.amazonaws.com';

        // build initial params array
        $params = array(
            'Action' => static::camelcase($method),
            'AWSAccessKeyId' => Config::get('amazonfps.access_key'),
            'SignatureVersion' => '2',
            'SignatureMethod' => 'HmacSHA256',
            'Timestamp' => static::timestamp(),
            'Version' => '2010-08-28',
        );
        
        // add method arguments
        $params = array_merge($params, $args[0]);
        
        // add signature
        $params['Signature'] = static::sign($params, $endpoint);
        
        // encode post data
        $query = http_build_query($params);
        
        // curl request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // not sure why, but 2 is correct
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $response = curl_exec($ch);

        // if errors...
        if (curl_errno($ch))
        {
            #$errors = curl_error($ch);
            curl_close($ch);

            // return false
            return false;
        }

        // if NO errors...
        else
        {
            curl_close($ch);

            // return array
            #parse_str($response, $result);
            return XML::from_string($response)->to_array();
        }
    }

    /**
     * Generate proper timestamp.
     *
     * @return  string
     */
    protected static function timestamp()
    {
        // generate timestamp (in UTC)
        date_default_timezone_set('UTC');
        $timestamp = strftime('%Y-%m-%d', time()).'T'.strftime('%T', time()).'Z';
        date_default_timezone_set(Config::get('application.timezone'));

        // return
        return $timestamp;
    }

    /**
     * Sign the query string using a secret key.
     *
     * @param   array $array
     * @param   string  $endpoint
     * @return  string
     */
    protected static function sign($array, $endpoint)
    {
        // sort array
        uksort($array, 'strcmp');
        
        // convert to query string
        $string = '';
        foreach ($array as $key => $value)
        {
            $string .= $key . '=' . rawurlencode($value) . '&';
        }
        $string = rtrim($string, '&');

        // make utf8
        $string = utf8_encode($string);
        
        // parse endpoint
        $endpoint = str_ireplace(array('http://', 'https://'), array('', ''), $endpoint);
        $parts = explode('/', $endpoint);

        // catch error...
        if (!is_array($parts)) trigger_error('Invalid endpoint for signing Amazon FPS communication.');

        // generate components
        $url = strtolower($parts[0]);
        $uri = strtolower(str_ireplace($url, '', $endpoint));

        // fix uri issue, if blank...
        if ($uri === '') $uri = '/';

        // add signature requirements to string
        $string = 'POST' . "\n" . $url . "\n" . $uri . "\n" . $string;
        
        // load key
        $key = Config::get('amazonfps.secret_key');

        // encrypt
        $string = hash_hmac('sha256', $string, $key, true);

        // encode
        $string = base64_encode($string);

        // return
        return $string;
    }

    /**
     * Convert a method name to camelcase.
     *
     * @param   string  $string
     * @return  string
     */
    protected static function camelcase($str)
    {
        return ucfirst(preg_replace('/(^|_)(.)/e', "strtoupper('\\2')", strval($str)));
    }

    /**
     * Helper for making Simple Payments buttons.
     *
     * @param   array  $params
     * @param   array  $submit_button_text
     * @return  string
     */
    public static function button($params, $submit_button_text = 'Make Payment')
    {
        // set endpoint
        $endpoint = Config::get('amazonfps.production_mode') ? 'https://authorize.payments.amazon.com/pba/paypipeline' : 'https://authorize.payments-sandbox.amazon.com/pba/paypipeline';

        // add access values to array
        $params['accessKey'] = Config::get('amazonfps.access_key');
        $params['amazonPaymentsAccountId'] = Config::get('amazonfps.account_id');

        // add signature values to array
        $params['signatureVersion'] = 2;
        $params['signatureMethod'] = 'HmacSHA256';

        // add signature
        $params['signature'] = static::sign($params, $endpoint);

        // build button
        $html = View::make('amazonfps::button')
            ->with('endpoint', $endpoint)
            ->with('params', $params)
            ->with('submit', $submit_button_text);

        // return
        return $html;
    }

}