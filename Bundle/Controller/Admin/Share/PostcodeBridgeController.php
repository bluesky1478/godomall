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

use Request;

/**
 * 도로명 주소 API 결과 처리 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class PostcodeBridgeController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // POST 파라메터
        $postValue = Request::post()->xss()->toArray();
        //--- 페이지 데이터
        try {
            // 구분자 처리
            $postValue['idCode'] = explode(STR_DIVISION, $postValue['gubun']);

            // addslashes 처리 (쿼터 처리후 스크립트로 넣어 줘야 해서. gd_htmlspecialchars_addslashes 는 적당치 않음)
            $postValue['road_address'] = addslashes($postValue['road_address']);
            $postValue['ground_address'] = addslashes($postValue['ground_address']);
            $postValue['address_sub'] = addslashes($postValue['address_sub']);

            // 주소 처리
            if ($postValue['s_type'] === 'road') {
                $postValue['address'] = $postValue['road_address'];
            }

            // 나머지 주소 처리
            if (substr($postValue['idCode'][1], -1) === ']') {
                if (strpos($postValue['idCode'][1], 'Add') === false) {
                    $postValue['idCodeSub'] = substr($postValue['idCode'][1], 0, -1) . 'Sub' . substr($postValue['idCode'][1], -1);
                } else {
                    if (strpos($postValue['idCode'][1], 'Add[') !== false) {
                        $postValue['idCodeSub'] = str_replace('Add[', 'SubAdd[', $postValue['idCode'][1]);
                    }
                    else if(strpos($postValue['idCode'][1], 'Address]') !== false){
                        $postValue['idCodeSub'] = str_replace('Address]', 'AddressSub]', $postValue['idCode'][1]);
                    }
                    else {
                        $postValue['idCodeSub'] = str_replace('Add[', 'SubAdd[', $postValue['idCode'][1]);
                    }
                }
            } else {
                $postValue['idCodeSub'] = $postValue['idCode'][1] . 'Sub';
            }

            // 구 우편번호 처리
            $postValue['zipcode'] = $postValue['zipcode1'] . '-' .  $postValue['zipcode2'];
            if (substr($postValue['idCode'][2], -1) === ']') {
                $postValue['idCodeText'] = str_replace('[', '', str_replace(']', '', $postValue['idCode'][2])) . 'Text';
            } else {
                $postValue['idCodeText'] = $postValue['idCode'][2] . 'Text';
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        echo '<!DOCTYPE html>' . PHP_EOL;
        echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">' . PHP_EOL;
        echo '<head>' . PHP_EOL;
        echo '<meta http-equiv="Content-Type" content="text/html;charset=' . SET_CHARSET . '"/>' . PHP_EOL;
        echo '<title>도로명 주소 API 결과 처리 페이지</title>' . PHP_EOL;
        echo '<meta name="robots" content="noindex, nofollow"/>' . PHP_EOL;
        echo '<meta name="robots" content="noarchive"/>' . PHP_EOL;
        echo '<script type="text/javascript">' . PHP_EOL;
        echo '<!--' . PHP_EOL;
        //레이어팝업 사용시
        echo 'if (typeof(window.top.layerSearchArea) == "object") {' . PHP_EOL;
        echo 'window.top.$(\'input[name="' .  $postValue['idCode'][0] . '"]\').val(\'' .  $postValue['zonecode'] . '\');' . PHP_EOL;
        echo 'window.top.$(\'input[name="' .  $postValue['idCode'][1] . '"]\').val(\'' .  $postValue['address'] . '\');' . PHP_EOL;
        echo 'window.top.$(\'input[name="' .  $postValue['idCodeSub'] . '"]\').val(\'' .  $postValue['address_sub'] . '\');' . PHP_EOL;
        echo 'window.top.$(\'input[name="' .  $postValue['idCode'][2] . '"]\').val(\'' .  $postValue['zipcode'] . '\');' . PHP_EOL;
        if (strlen($postValue['zipcode']) == 7) {
            echo 'window.top.$(\'#' .  $postValue['idCodeText'] . '\').show();' . PHP_EOL;
            echo 'window.top.$(\'#' .  $postValue['idCodeText'] . '\').html(\'(' .  $postValue['zipcode'] . ')\');' . PHP_EOL;
        } else {
            echo 'window.top.$(\'#' .  $postValue['idCodeText'] . '\').hide();' . PHP_EOL;
        }
        echo 'if (typeof(parent.postcode_callback) == \'function\') parent.postcode_callback();';
        echo 'if (typeof(window.top.layerSearch) == "object") window.top.layerSearch.removeChild(window.top.layerSearch.firstChild);' . PHP_EOL;
        //윈도우팝업 사용시
        echo '} else {' . PHP_EOL;
        echo 'parent.opener.$(\'input[name="' .  $postValue['idCode'][0] . '"]\').val(\'' .  $postValue['zonecode'] . '\');' . PHP_EOL;
        echo 'parent.opener.$(\'input[name="' .  $postValue['idCode'][1] . '"]\').val(\'' .  $postValue['address'] . '\');' . PHP_EOL;
        echo 'parent.opener.$(\'input[name="' .  $postValue['idCodeSub'] . '"]\').val(\'' .  $postValue['address_sub'] . '\');' . PHP_EOL;
        echo 'parent.opener.$(\'input[name="' .  $postValue['idCode'][2] . '"]\').val(\'' .  $postValue['zipcode'] . '\');' . PHP_EOL;
        if (strlen($postValue['zipcode']) == 7) {
            echo 'parent.opener.$(\'#' .  $postValue['idCodeText'] . '\').show();' . PHP_EOL;
            echo 'parent.opener.$(\'#' .  $postValue['idCodeText'] . '\').html(\'(' .  $postValue['zipcode'] . ')\');' . PHP_EOL;
        } else {
            echo 'parent.opener.$(\'#' .  $postValue['idCodeText'] . '\').hide();' . PHP_EOL;
        }
        echo 'if (typeof(parent.opener.postcode_callback) == \'function\') parent.opener.postcode_callback("' .  $postValue['idCode'][0] . '");';
        echo 'window.parent.close();' . PHP_EOL;
        echo '}';
        echo '//-->' . PHP_EOL;
        echo '</script>' . PHP_EOL;
        echo '</head>' . PHP_EOL;
        echo '<body>' . PHP_EOL;
        echo '...처리중...' . PHP_EOL;
        echo '</body>' . PHP_EOL;
        echo '</html>' . PHP_EOL;
        exit();
    }
}
