<?php
header('Access-Control-Allow-Origin:*');

$config = array(
    'APP_ID' => 'wx9e6b51416c7b5669',
    'APP_SECRET' => 'e39ae293a9b52c9173104d1418cbbc4b',
    'TEMPLATE_ID' => 'QCMouws8XCPRXt8ftBzdhB2o650JKmgLGn2Ac1Syh3c',
    'user' => array(
        array(
            'name' => '001',
            'id' => '-Nina-H',
            'date' => '2022-08-28',
            'city' => '合肥',
            'birthday_1' => '06-17',
            'birthday_2' => '12-06',
            'TEMPLATE_ID' => 'QCMouws8XCPRXt8ftBzdhB2o650JKmgLGn2Ac1Syh3c',
        )
    ),
    'MASTER_ID' => 'oHp5U6BiEWI-1Eoh-Uq0KJL2q8aY',
    'MASTER_TEMPLATE_ID' => 'Kdc-fwEpCFYVydXZzRDpr_0esIvA0NdEZcgg0pKIN5c',
);

$getToken = json_decode(GET("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=". $config['APP_ID'] ."&secret=". $config['APP_SECRET']), true)['access_token'];

$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $getToken;

$userList = array('success' => array(), 'error' => array());

foreach ($config['user'] as $k => $v){
    $weather = getWeather($v['city'])['weather'];
    $temp = getWeather($v['city'])['temp'] . '℃';
    $tempRange = getWeather($v['city'])['low'] . '℃~' . getWeather($v['city'])['high'] . '℃';
    $loveDay = getLoveDay($v['date']);
    $birthdayDay_1 = getBirthday($v['birthday_1']);
    $birthdayDay_2 = getBirthday($v['birthday_2']);
    $rainbow = getRainbow();

    $data = array(
        'touser' => $v['id'],
        'template_id' => $templateID = $v['TEMPLATE_ID'] ? $v['TEMPLATE_ID'] : $config['TEMPLATE_ID'],
        'data' => array(
            'weather' => array(
                'value' => $weather,
                'color' => getRandomColor(),
            ),
            'temp' => array(
                'value' => $temp,
                'color' => getRandomColor(),
            ),
            'tempRange' => array(
                'value' => $tempRange,
                'color' => getRandomColor(),
            ),
            'loveDay' => array(
                'value' => $loveDay,
                'color' => getRandomColor(),
            ),
            'birthDay' => array(
                'value' => $birthdayDay_1,
                'color' => getRandomColor(),
            'preDay'=> array(
                'value' => $birthdayDay_2,
                'color' => getRandomColor(),
            ),
            'rainbow' => array(
                'value' => $rainbow,
                'color' => getRandomColor(),
            ),
        ),
    );
    $json = json_decode(POST($url, $data), true);
    if($json['errcode'] == 0){
        array_push($userList['success'], $v['name']);
    }else{
        array_push($userList['error'], $v['name']);
    }
}

$success = implode(',', $userList['success']);
$error = implode(',', $userList['error']);
$successNum = count($userList['success']);
$errorNum = count($userList['error']);

$data = array(
    'touser' => $config['MASTER_ID'],
    'template_id' => $config['MASTER_TEMPLATE_ID'],
    'data' => array(
        'count' => array(
            'value' => $successNum + $errorNum,
        ),
        'success' => array(
            'value' => $success,
        ),
        'error' => array(
            'value' => $error,
        ),
        'successNum' => array(
            'value' => $successNum,
        ),
        'errorNum' => array(
            'value' => $errorNum,
        ),
    ),
);

$json = json_decode(POST($url, $data), true);

if($json['errcode'] == 0){
    $json = array(
        'code' => 200,
        'msg' => '执行完成'
    );
}else{
    $json = array(
        'code' => 201,
        'msg' => '执行失败'
    );
}

echo json_encode($reJson, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

function getRainbow()
{
    $url = 'https://tenapi.cn/chp/';
    $Json = json_decode(GET($url), true);
    return $Json['data']['text'];
}

function getWeather($city)
{
    $url = 'http://autodev.openspeech.cn/csp/api/v2.1/weather?openId=aiuicus&clientType=android&sign=android&city='. $city;
    $Json = json_decode(GET($url), true);
    return $Json['data']['list'][0];
}

function getLoveDay($time)
{
    $time = time() - strtotime($time);
    $day = floor($time / (24 * 3600));
    return $day;
}

function getBirthday($birthday)
{
    $today = time();
    $next = strtotime(date('Y') . '-' . $birthday);
    if ($next < $today) {
        $next = strtotime(date('Y') + 1 . '-' . $birthday);
    }
    return ceil(($next - $today) / (24 * 3600));
}

function getRandomColor()
{
    $str = '0123456789abcdef';
    $color = '#';
    for ($i = 0; $i < 6; $i++) {
        $color .= $str[mt_rand(0, 15)];
    }
    return $color;
}

function GET($url)
{
    $ch=curl_init((string)$url);
    curl_setopt($ch,CURLOPT_HEADER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,5000);
    $result = curl_exec($ch);
    return $result;
}

function POST($url, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
