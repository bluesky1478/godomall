<?php

namespace Bundle\Component\PlusShop\WeatherWidget;


use App;
use Framework\Utility\HttpUtils;
use Framework\Utility\KafkaUtils;
use Framework\Utility\MessageQueueUtils;
use UserFilePath;

class WeatherWidgetConfig
{
    const URL = 'https://relay.godo.co.kr/weather';
    const __ENC_SECURE_KEY__ = 'qaSkmFYwA7MFVbFPysw8gyQLKHvxrXyY';
    const CACHE_PATH = 'plusshop/nhngodo/weather_widget_free/cache';

    private $locations = [
        '강원' => [
            'AreaCode' => 'A1'
        ],
        '경기' => [
            'AreaCode' => 'A2'
        ],
        '경남' => [
            'AreaCode' => 'A3'
        ],
        '경북' => [
            'AreaCode' => 'A4'
        ],
        '광주' => [
            'AreaCode' => 'A5'
        ],
        '대구' => [
            'AreaCode' => 'A6'
        ],
        '대전' => [
            'AreaCode' => 'A7'
        ],
        '부산' => [
            'AreaCode' => 'A8'
        ],
        '서울' => [
            'AreaCode' => 'A9'
        ],
        '세종' => [
            'AreaCode' => 'A10'
        ],
        '울산' => [
            'AreaCode' => 'A11'
        ],
        '인천' => [
            'AreaCode' => 'A12'
        ],
        '전남' => [
            'AreaCode' => 'A13'
        ],
        '전북' => [
            'AreaCode' => 'A14'
        ],
        '제주' => [
            'AreaCode' => 'A15'
        ],
        '충남' => [
            'AreaCode' => 'A16'
        ],
        '충북' => [
            'AreaCode' => 'A17'
        ]
    ];

    private $queryCode = [
        'forecastGrib' => 'Q1',
        'forecastSpaceData' => 'Q2',
        'middleLandWeather' => 'Q3',
        'middleTemperature' => 'Q4'
    ];

    public function isActive() {
        $dao = new WeatherWidgetDao();
        $data = $dao->getData();

        if (!isset($data['active'])) {
            return true;
        }

        return $data['active'];
    }

    public function getLocationKeys()
    {
        return array_keys($this->locations);
    }

    public function getAreaCode($location)
    {
        return $this->locations[$location]['AreaCode'];
    }

    public function getQueryCode($query)
    {
        return $this->queryCode[$query];
    }

    public function getWeather($baseLocation)
    {
        $cache = $this->getCache($baseLocation);
        if ($cache && $cache['time_at']) {
            if (time() - strtotime($cache['time_at']) < 60 * 60) {
                return $cache;
            }
        }

        $forecastGrib = $this->callApi($baseLocation, 'forecastGrib');
        $forecastSpaceData = $this->callApi($baseLocation, 'forecastSpaceData');
        $middleLandWeather = $this->callApi($baseLocation, 'middleLandWeather');
        $middleTemperature = $this->callApi($baseLocation, 'middleTemperature');

        $weatherInfo = [[], []];
        $shortWeatherCode = ["T1H", "SKY", "PTY", "LGT", "REH"];

        foreach ($forecastGrib as $key => $value) {
            if (in_array($value['category'], $shortWeatherCode) && !isset($weatherInfo[0][$value['category']])) {
                $weatherInfo[0][$value['category']] = $value['obsrValue'];
            }
        }

        if (empty($weatherInfo[0])) {
            return $cache;
        }

        $today = date('Ymd');
        $tomorrow = date('Ymd', strtotime('tomorrow'));

        foreach ($forecastSpaceData as $value) {
            $fcstDate = $value['fcstDate'];
            $fcstValue = $value['fcstValue'];
            $category = $value['category'];

            if ($fcstDate == $today && !isset($weatherInfo[0][$category])) {
                $weatherInfo[0][$category] = $fcstValue;
            } else if ($fcstDate == $tomorrow && !isset($weatherInfo[1][$category])) {
                $weatherInfo[1][$category] = $fcstValue;
            }
        }

        if (empty($weatherInfo[0]['TMN'])) {
            $weatherInfo[0]['TMN'] = $weatherInfo[1]['TMN'];
        }
        if (empty($weatherInfo[0]['TMX'])) {
            $weatherInfo[0]['TMX'] = $weatherInfo[1]['TMX'];
        }

        if (empty($weatherInfo[1])) {
            return $cache;
        }

        $week_ground_code = array('wf3Am', 'wf4Am', 'wf5Am', 'wf6Am', 'wf7Am');
        $week_max_temp_code = array('taMax3', 'taMax4', 'taMax5', 'taMax6', 'taMax7');
        $week_min_temp_code = array('taMin3', 'taMin4', 'taMin5', 'taMin6', 'taMin7');
        for ($i = 2; $i <= 6; $i++) {
            $weatherInfo[$i] = array();
            $weatherInfo[$i]['status'] = $middleLandWeather[$week_ground_code[$i - 2]];
            $weatherInfo[$i]['TMX'] = $middleTemperature[$week_max_temp_code[$i - 2]];
            $weatherInfo[$i]['TMN'] = $middleTemperature[$week_min_temp_code[$i - 2]];
        }

        $weatherInfo[0]['code'] = $this->getTodayWeatherCode($weatherInfo[0]);
        unset($weatherInfo[0]['LGT']);
        unset($weatherInfo[0]['PTY']);
        unset($weatherInfo[0]['SKY']);

        $weatherInfo[1]['code'] = $this->getShortTermStatusCode(
            $weatherInfo[1]['SKY'], $weatherInfo[1]['PTY'], $weatherInfo[1]['LGT']);
        unset($weatherInfo[1]['PTY']);
        unset($weatherInfo[1]['SKY']);

        for ($i = 2; $i <= 6; $i++) {
            $weatherInfo[$i]['code'] = $this->getWeatherStatusCode($weatherInfo[$i]['status']);
            unset($weatherInfo[$i]['status']);
        }

        $weatherInfo['time_at'] = date("Y-m-d H:i:s");

        $this->saveCache($weatherInfo, $baseLocation);

        return $weatherInfo;
    }

    private function getTodayWeatherCode($weatherInfoZero)
    {
        $pty = $weatherInfoZero['PTY'];
        $sky = $weatherInfoZero['SKY'];
        $lgt = $weatherInfoZero['LGT'];
        $code = null;

        if ($lgt == 1) {
            $code = 10;
        } else if ($sky == 1) {
            $code = $pty == 0 ? 0 : null;
        } else if ($sky == 2) {
            $code = $pty == 0 ? 1 : null;
        } else if ($sky == 3) {
            if ($pty == 0) {
                $code = 2;
            } else if ($pty == 1) {
                $code = 3;
            } else if ($pty == 2) {
                $code = 4;
            } else if ($pty == 3) {
                $code = 5;
            } else {
                $code = null;
            }
        } else if ($sky == 4) {
            if ($pty == 0) {
                $code = 6;
            } else if ($pty == 1) {
                $code = 7;
            } else if ($pty == 2) {
                $code = 8;
            } else if ($pty == 3) {
                $code = 9;
            } else {
                $code = null;
            }
        }

        return $code;
    }

    private function getWeatherStatusCode($statusText)
    {
        switch ($statusText) {
            case '맑음':
                $code = 0;
                break;
            case '구름조금':
                $code = 1;
                break;
            case '구름많음':
                $code = 2;
                break;
            case '구름많고 비':
                $code = 3;
                break;
            case '구름많고 비/눈':
                $code = 4;
                break;
            case '구름많고 눈/비':
                $code = 4;
                break;
            case '구름많고 눈':
                $code = 5;
                break;
            case '흐림':
                $code = 6;
                break;
            case '흐리고 비':
                $code = 7;
                break;
            case '흐리고 비/눈':
                $code = 8;
                break;
            case '흐리고 눈/비':
                $code = 8;
                break;
            case '흐리고 눈':
                $code = 9;
                break;
            case '낙뢰':
                $code = 10;
                break;
            default:
                $code = 0;
        }

        return $code;
    }

    private function getShortTermStatusCode($sky, $pty, $lgt)
    {
        $code = null;

        if ($lgt == 1) {
            $code = 10;
        } else if ($sky == 1 && $pty == 0) {
            $code = 0;
        } else if ($sky == 2 && $pty == 0) {
            $code = 1;
        } else if ($sky == 3) {
            switch ($pty) {
                case 0:
                    $code = 2;
                    break;
                case 1:
                    $code = 3;
                    break;
                case 2:
                    $code = 4;
                    break;
                case 3:
                    $code = 5;
                    break;
            }
        } else if ($sky == 4) {
            switch ($pty) {
                case 0:
                    $code = 6;
                    break;
                case 1:
                    $code = 7;
                    break;
                case 2:
                    $code = 8;
                    break;
                case 3:
                    $code = 9;
                    break;
            }
        }

        return $code;
    }

    private function callApi($baseLocation, $query)
    {
        $params = [
            'AreaCode' => $this->getAreaCode($baseLocation),
            'QueryCode' => $this->getQueryCode($query)
        ];
        $encData = $this->getEncryptedString($params);

        $uri = self::URL . "/$query?enc-data=$encData";

        $option['opt_post'] = false;
        $option['opt_connecttimeout'] = 3;

        $response = HttpUtils::httpOauthCurl($uri, $params, $option);
        $resultCheck = json_decode($response, true);

        if ($response === false || $resultCheck['error'] == "invalid_token") {
            HttpUtils::authTokenGenerate(true);
            $request = \App::getInstance('request');
            $ip = $request->getRemoteAddress();
            $date = date('Y.m.d H:i:s');
            if ($resultCheck['error'] == "invalid_token") {
                $msg = $resultCheck['error'];
                $message = "이름 : 날씨 위젯 인증\n내용 : 날씨 중계서버 API 인증 실패 $msg\n날짜 : $date\nHOST IP : $ip\n요청 URI : $uri";
            } else {
                $message = "이름 : 날씨 위젯\n내용 : 날씨 중계서버 API 요청 실패\n날짜 : $date\nHOST IP : $ip\n요청 URI : $uri";
            }
            $options = [
                'TAGS' => ['PlusShop', 'weather-widget'],
                'SEND_NOTIFICATION' => true
            ];
            KafkaUtils::sendLog($message, KafkaUtils::LEVEL_ERROR, null, null, null, $options);
        }

        $result = json_decode($response, true);

        return $result['items'];
    }

    private function getEncryptedString($data)
    {
        $data['__APPEND__'] = array(
            'SECURE_KEY' => self::__ENC_SECURE_KEY__,
            'DATE' => date('Y-m-d H:i:s'),
            'PREFIX' => substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'),
                0, rand(5, 9))
        );

        return strlen($data['__APPEND__']['PREFIX']) . $data['__APPEND__']['PREFIX'] . base64_encode(json_encode($data));
    }

    private function getCache($baseLocation)
    {
        $filename = $this->getCacheFilename($baseLocation);

        if (!is_file($filename)) {
            return null;
        }

        $cache = json_decode(file_get_contents($filename), true);

        return $cache['data'];
    }

    private function saveCache($data, $baseLocation)
    {
        $path = $this->getCachePath();

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $filename = $this->getCacheFilename($baseLocation);

        return file_put_contents($filename, json_encode([
            'data' => $data,
            'ttl' => 0
        ]));
    }

    private function getCachePath()
    {
        return UserFilePath::data(
                ...explode('/', self::CACHE_PATH))->getRealPath();
    }

    private function getCacheFilename($baseLocation)
    {
        return $this->getCachePath() . '/' . sha1($baseLocation) . '.cache';
    }
}
