<?php
/*
* Copyright (c) 2017 Baidu.com, Inc. All Rights Reserved
*
* Licensed under the Apache License, Version 2.0 (the "License"); you may not
* use this file except in compliance with the License. You may obtain a copy of
* the License at
*
* Http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
* WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
* License for the specific language governing permissions and limitations under
* the License.
*/

/**
 * Http Client
 */
class AipHttpClient{

    /**
     * HttpClient
     * @param array $headers HTTP header
     */
    public function __construct($headers=array()){
        $this->headers = $this->buildHeaders($headers);
    }

    /**
     * @param  string $url
     * @param  array $data HTTP POST BODY
     * @param  array $param HTTP URL
     * @param  array $headers HTTP header
     * @return array
     */
    public function post($url, $data=array(), $params=array(), $headers=array()){
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array(
            'code' => $code,
            'content' => $content,
        );
    }

    /**
     * @param  string $url
     * @param  array $param HTTP URL
     * @param  array $headers HTTP header
     * @return array
     */
    public function get($url, $params=array(), $headers=array()){
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array(
            'code' => $code,
            'content' => $content,
        );
    }

    /**
     * 构造 header
     * @param  array $headers
     * @return array
     */
    private function buildHeaders($headers){
        $result = array();
        foreach($headers as $k => $v){
            $result[] = sprintf('%s:%s', $k, $v);
        }
        return $result;
    }

    /**
     * 
     * @param  string $url
     * @param  array $params 参数
     * @return string
     */
    private function buildUrl($url, $params){
        if(!empty($params)){
            $str = http_build_query($params);
            return $url . (strpos($url, '?') === false ? '?' : '&') . $str;
        }else{
            return $url;
        }
    }
}