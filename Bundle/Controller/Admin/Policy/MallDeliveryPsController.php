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

namespace Bundle\Controller\Admin\Policy;

use Component\Delivery\OverseasDelivery;
use Component\Mall\MallDAO;
use Exception;
use Request;

/**
 * Class MallConfigPsController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MallDeliveryPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $overseasDelivery = new OverseasDelivery();
        $mallDao = MallDAO::getInstance();
        \Logger::debug(__METHOD__, Request::request()->all());
        try {
            switch (Request::request()->get('mode', '')) {
                // 해외배송조건 설정 수정
                case 'modify':
                    $overseasDelivery->setBasicView(Request::post()->all());
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.replace("../policy/mall_delivery_list.php");');
                    break;

                // 해외배송 그룹 설정 등록/수정
                case 'groupModify':
                    $overseasDelivery->setCountryGroupView(Request::post()->all());
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.replace("../policy/mall_delivery_group_list.php");');
                    break;

                // 해외배송 그룹 설정 삭제
                case 'groupDelete':
                    $overseasDelivery->deleteCountryGroups(Request::post()->get('deliverChk'));
                    $this->layer(__('삭제가 완료되었습니다.'), 'top.location.replace("../policy/mall_delivery_group_list.php");');
                    break;

                // 국가정보 가져오기
                case 'getCountriesView':
                    $data = $mallDao->selectCountries(Request::get()->get('code'));
                    $this->json(
                        [
                            'result' => true,
                            'data' => $data,
                        ]
                    );
                    break;

                // 기타
                default:
                    break;
            }
        } catch (Exception $e) {
            \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if (Request::isAjax()) {
                $this->json(
                    [
                        'result'  => false,
                        'message' => $e->getMessage(),
                    ]
                );
            }
        }
    }
}
