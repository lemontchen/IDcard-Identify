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

require_once 'lib/AipBase.php';

/**
 * 文字OCR
 */
class AipOcr extends AipBase{

    /**
     * idcard api url
     * @var string
     */
    private $idcardUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/idcard';
    
    /**
     * bankcard api url
     * @var string
     */
    private $bankcardUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/bankcard';
    
    /**
     * general api url
     * @var string
     */
    private $generalUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/general';

    /**
     * @param  string $image 图像读取
     * @param  bool $isFront 身份证是 true正面 false反面
     * @param  array $options 可选参数
     * @return array
     */
    protected function idcardApi($image, $isFront, $options=array()){
        $headers = $this->getAuthHeaders('POST', $this->idcardUrl);

        $params = array();
        if(!empty($this->accessToken)){
            $params['access_token'] = $this->accessToken;
        }

        $data = array();
        $data['image'] = base64_encode($image);
        $data['id_card_side'] = $isFront ? 'front' : 'back';
        $data['detect_direction'] = isset($options['detectDirection']) ? $options['detectDirection'] : 'false';
        $data['accuracy'] = isset($options['accuracy']) ? $options['accuracy'] : 'auto';

        return $this->client->post($this->idcardUrl, $data, $params, $headers);
    }
    
    /**
     * @param  string $image 图像读取
     * @return array
     */
    protected function bankcardApi($image){
        $headers = $this->getAuthHeaders('POST', $this->bankcardUrl);

        $params = array();
        if(!empty($this->accessToken)){
            $params['access_token'] = $this->accessToken;
        }

        $data = array();
        $data['image'] = base64_encode($image);

        return $this->client->post($this->bankcardUrl, $data, $params, $headers);
    }

    /**
     * @param  string $image 图像读取
     * @param  array $options 可选参数
     * @return array
     */
    protected function generalApi($image, $options=array()){
        $headers = $this->getAuthHeaders('POST', $this->generalUrl);
        
        $params = array();
        if(!empty($this->accessToken)){
            $params['access_token'] = $this->accessToken;
        }

        $data = array();
        $data['image'] = base64_encode($image);
        $data['recognize_granularity'] = isset($options['recognize_granularity']) ? $options['recognize_granularity'] : 'big';
        $data['mask'] = isset($options['mask']) ? base64_encode($options['mask']) : '';
        $data['language_type'] = isset($options['language_type']) ? $options['language_type'] : 'CHN_ENG';
        $data['detect_direction'] = isset($options['detect_direction']) ? $options['detect_direction'] : 'false';
        $data['detect_language'] = isset($options['detect_language']) ? $options['detect_language'] : 'false';
        $data['classify_dimension'] = isset($options['classify_dimension']) ? $options['classify_dimension'] : 'lottery';
        $data['vertexes_location'] = isset($options['vertexes_location']) ? $options['vertexes_location'] : 'false';   
        
        return $this->client->post($this->generalUrl, $data, $params, $headers);
    }
}
