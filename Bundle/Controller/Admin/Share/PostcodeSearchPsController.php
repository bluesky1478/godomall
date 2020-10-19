<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Share;

use Component\Godo\GodoCenterServerApi;
use Request;

/**
 * 도로명 주소 검색
 * @author donghyun <dong7330@godo.co.kr>
 */
class PostcodeSearchPsController extends \Controller\Admin\Controller
{
    /**
     * index
     */
    public function index()
    {
        try {
            $return = array();
            $post = Request::post()->toArray();
            $postcode = new GodoCenterServerApi();
            $sqlFilter = array("OR", "SELECT", "INSERT", "DELETE", "UPDATE", "CREATE", "DROP", "EXEC", "UNION",  "FETCH", "DECLARE", "TRUNCATE");

            // - , 공백 제외 특수문자 필터
            $post['keyword'] = preg_replace("/[#\&\+\%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $post['keyword']);

            // sql 예약어 필터
            foreach ($sqlFilter as $v) {
                if (strpos(strtoupper($post['keyword']), $v)) {
                    exit;
                }
            }

            if ($post['mode'] == 'areaDelivery') {
                $result = $postcode->getCurlDataPostcodeV2($post['keyword'], true);
                $result = json_decode($result['true'], true);
                foreach ($result['resultData']['addressData'] as $k => $v) {

                    if (empty($v['roadAddress']) === true) {
                        $tmp = $this->cutAddress($v['groundAddress']);
                    }
                    else {
                        $tmp = $this->cutAddress($v['roadAddress']);
                    }
                    if (array_search($tmp, $return) === false) {
                        $return[] = $tmp;
                    }
                }

                $return = json_encode($return, JSON_UNESCAPED_UNICODE);
                echo $return;
            }
            else {
                $result = $postcode->getCurlDataPostcodeV2($post['keyword']);
                echo $result['true'];
            }
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    private function cutAddress($address)
    {
        $sido = array('시', '도');
        $sigugun = array('시', '구', '군');
        $dongubmyun = array('동', '읍', '면');
        $rogil = array('로', '길');

        $arr = explode(' ',$address);
        for ($i = 0; $i < count($arr); $i++) {
            $tempStr = mb_substr($arr[$i], -1, 1, 'UTF-8');
            if (array_search($tempStr, $sido) !== false) {
                $return[$i] = $arr[$i];
            }
            else if (array_search($tempStr, $sigugun) !== false) {
                $return[$i] = $arr[$i];
            }
            else if (array_search($tempStr, $dongubmyun) !== false) {
                $return[$i] = $arr[$i];
            }
            else if (array_search($tempStr, $rogil) !== false) {
                $return[$i] = $arr[$i];
                break;
            }
        }

        return implode($return, ' ');
    }
}