<?php

// 引入文字识别OCR SDK
require_once '../AipOcr.php';

// 定义常量
const APP_ID = '你的 App ID';
const API_KEY = '你的 Api Key';
const SECRET_KEY = '你的 Secret Key';

// 初始化
$aipOcr = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

// 身份证识别
var_dump($aipOcr->idcard(file_get_contents('idcard.jpg'), true));

// 银行卡识别 
// var_dump($aipOcr->bankcard(file_get_contents('bankcard.jpg')));

// 通用文字识别
// var_dump($aipOcr->general(file_get_contents('general.png')));
