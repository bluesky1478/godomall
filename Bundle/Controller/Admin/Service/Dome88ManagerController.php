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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Service;

use Component\Godo\GodoCenterServerApi;
use Framework\Debug\Exception\AlertRedirectException;

/**
 * 도매88 판매관리
 *
 * @author Sunny <bluesunh@godo.co.kr>
 */
class Dome88ManagerController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('service', 'goods', 'dome88Manager');

        try {
            $godoCenterServiceApi = new GodoCenterServerApi();
            $result = $godoCenterServiceApi->getDome88Login();
            $data = (array) json_decode($result->msg);

            if ($result->code == '200' && gd_isset($data['secretKey'])) {
                $data['data'] = json_encode($data['data']);
                $data['action'] = 'https://dome88.godo.co.kr/api';
                // $data['action'] = 'http://test.shopbox.kaffalab.com/api'; // Test
            } else {
                throw new AlertRedirectException(__('도매88 판매관리 서비스가 신청되어 있지 않습니다. 신청 페이지로 이동합니다.'), null, null, '../service/service_info.php?menu=goods_dome88_info');
            }
            $this->setData('data', $data);
        } catch(AlertOnlyException $e) {
            throw new AlertOnlyException($e->getMessage());
        }
    }
}
