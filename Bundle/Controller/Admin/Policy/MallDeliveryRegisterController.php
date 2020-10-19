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
use Component\Mall\Mall;
use Request;

/**
 * Class MallConfigController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MallDeliveryRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 관리자 메뉴 설정
        $this->callMenu('policy', 'multiMall', 'mallDeliveryRegist');

        // 해외배송 콤포넌트 호출
        $overseasDelivery = new OverseasDelivery();

        // 수정할 배송조건 설정
        $data = $overseasDelivery->getBasicView(Request::get()->get('sno'));
        $this->setData('data', $data['data']);
        $this->setData('group', $data['group']);
        $this->setData('checked', $data['checked']);
        $this->setData('selected', $data['selected']);

        // 배송국가 그룹 리스트
        $countryGroupData = [];
        foreach ($overseasDelivery->getDao()->selectCountryGroup() as $key => $val) {
            $countryGroupData[$val['sno']] = $val['groupName'];
        }
        $this->setData('countryGroupData', $countryGroupData);

        // 배송비조건 셀렉트 박스용 데이터 생성
        $scmDeliveryData = [];
        foreach ($overseasDelivery->getDao()->selectScmDeliveryBasic() as $key => $val) {
            $scmDeliveryData[$val['sno']] = $val['method'];
        }
        $this->setData('scmDeliveryData', $scmDeliveryData);
    }
}
