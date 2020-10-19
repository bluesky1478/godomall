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

namespace Bundle\Controller\Mobile\Share;

use Component\Godo\GodoCenterServerApi;
use Request;

/**
 * 도로명 주소 검색
 * @author donghyun <dong7330@godo.co.kr>
 */
class PostcodeSearchPsController extends \Controller\Mobile\Controller
{
    /**
     * index
     */
    public function index()
    {
        try {
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

            $result = $postcode->getCurlDataPostcodeV2($post['keyword']);

            echo $result['true'];
            exit;
        }
        catch (Exception $e) {
            throw $e;
        }
    }
}