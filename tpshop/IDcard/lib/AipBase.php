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

require_once 'AipHttpClient.php';
require_once 'AipBCEUtil.php';

/**
 * Aip Base 基类
 */
class AipBase {

    /**
     * 获取access token url
     * @var string
     */
    protected $accessTokenUrl = 'https://aip.baidubce.com/oauth/2.0/token';

    /**
     * appId
     * @var string
     */
    protected $appId = '';

    /**
     * apiKey
     * @var string
     */
    protected $apiKey = '';
    
    /**
     * secretKey
     * @var string
     */
    protected $secretKey = '';
    
    /**
     * [ccessToken
     * @var string
     */
    protected $accessToken = '';

    /**
     * @param string $appId 
     * @param string $apiKey
     * @param string $secretKey
     */
    public function __construct($appId, $apiKey, $secretKey){
        $this->appId = trim($appId);
        $this->apiKey = trim($apiKey);
        $this->secretKey = trim($secretKey);
        $this->accessToken = '';

        //apiKey长度 云的老用户是 32或者其他  openapi的是24
        $this->isCloudUser = strlen($this->apiKey) == 32 ? true : false;
        $this->client = new AipHttpClient();
    }

    /**
     * 代理调用Api结尾的方法
     * @param  string $method 方法名称
     * @param  array $args 参数
     * @return mixed
     */
    public function __call($method, $args){
        //真实调用的方法
        $realMethod = preg_match('/Api$/i', $method) ? $method : $method . 'Api';

        //不存在的方法抛出异常
        if(!method_exists($this, $realMethod)){
            throw new Exception(sprintf('Call to undefined method %s::%s()', get_class($this), $method));
        }

        //不是云的老用户 且 没有access token就获取
        if(!$this->isCloudUser && empty($this->accessToken)){
            $this->accessToken = $this->generateAccessToken();
        }

        //调用Api方法
        $response = call_user_func_array(array($this, $realMethod), $args);
        // var_dump($response);

        //openapi 用户
        if(!$this->isCloudUser){
            $obj = json_decode($response['content'], true);

            //access token 失效 则重新获取 后再次调用Api
            if(isset($obj['error_code']) && $obj['error_code'] == 110){
                $this->accessToken = $this->generateAccessToken(true);

                $response = call_user_func_array(array($this, $realMethod), $args);
            }

        }

        $result = $this->proccess_result($response['content']);

        return $result === null ? array() : $result;
    }

    /**
     * 返回 access token 路径
     * @return string
     */
    protected function getAccessTokenPath(){
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . md5($this->apiKey);
    }

    /**
     * 格式化结果
     * @param $content string
     * @return mixed
     */
    protected function proccess_result($content){
        return json_decode($content, true);
    }

    /**
     * 获取 access token
     * @param bool $refresh 是否刷新
     * @return string
     */
    private function generateAccessToken($refresh=false){

        if(!$refresh){
            $obj = json_decode(@file_get_contents($this->getAccessTokenPath()), true);
            if(is_array($obj) && $obj['time'] + $obj['expires_in'] - 30 > time()){
                return $obj['access_token'];
            }
        }

        $response = $this->client->post($this->accessTokenUrl, array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->apiKey,
            'client_secret' => $this->secretKey,
        ));

        $obj = json_decode($response['content'], true);
        // print json_encode($obj, JSON_PRETTY_PRINT);

        if(!isset($obj['access_token'])){
            //获取失败 则认为是 云的老用户 
            $this->isCloudUser = true;

            return '';
        }else{
            $obj['time'] = time();
            @file_put_contents($this->getAccessTokenPath(), json_encode($obj));

            return $obj['access_token'];
        }
    }

    /**
     * @param  string $method HTTP method
     * @param  string $url
     * @param  array $param 参数
     * @return array
     */
    protected function getAuthHeaders($method, $url, $params=array()){
        //不是云的老用户则不用在header中签名 认证
        if(!$this->isCloudUser){
            return array();
        }

        $obj = parse_url($url);
        //UTC 时间戳
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $headers = array(
            'Host' => isset($obj['port']) ? sprintf('%s:%s', $obj['host'], $obj['port']) : $obj['host'],
            'x-bce-date' => $timestamp,
            'accept' => '*/*',
        );

        //签名
        $headers['authorization'] = AipSampleSigner::sign(array(
            'ak' => $this->apiKey,
            'sk' => $this->secretKey,
        ), $method, $obj['path'], $headers, $params, array(
            'timestamp' => $timestamp,
            'headersToSign' => array(
                'host',
            ),
        ));

        return $headers;
    }

}