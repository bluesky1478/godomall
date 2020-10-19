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

namespace Bundle\Controller\Admin\Member;

use Component\Member\Group\Util;
use Component\Member\MemberGroup;
use Framework\Debug\Exception\AlertBackException;

/**
 * Class MemberGroupRegisterController
 * @package Controller\Admin\Member
 * @author  yjwee
 */
class MemberGroupRegisterController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException 오류메시지
     * @return void
     */
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        $this->callMenu('member', 'member', 'groupRegister');
        $groupUtil = new Util();

        //--- 브랜드 정보
        $cate = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
        $getBrandData = $cate->getCategoryListSelectBox('y');

        try {
            $getData = [];
            $getData['groupIconHtml'] = $groupUtil->groupIconToWebPath(null);
            $getData['groupImageHtml'] = $groupUtil->groupImageToWebPath(null);

            gd_isset($getData['groupMarkGb'], 'text');
            gd_isset($getData['groupImageGb'], 'none');
            gd_isset($getData['settleGb'], 'all');
            gd_isset($getData['dcType'], 'percent');
            gd_isset($getData['overlapDcType'], 'percent');
            gd_isset($getData['mileageType'], 'percent');
            gd_isset($getData['fixedRatePrice'], 'price');
            gd_isset($getData['apprExclusionOfRatingFl'], 'n');

            $jsonDecodeKeysByGroup = [
                'fixedRateOption',
                'dcExOption',
                'overlapDcOption',
                'dcExScm',
                'dcExCategory',
                'dcExBrand',
                'dcExGoods',
                'overlapDcScm',
                'overlapDcCategory',
                'overlapDcBrand',
                'overlapDcGoods',
            ];
            $getData = gd_array_json_decode($getData, $jsonDecodeKeysByGroup);
            $getData = gd_htmlspecialchars($getData);
            $jsonEncodeKeysByGroup = [
                'dcExScm',
                'dcExCategory',
                'dcExBrand',
                'dcExGoods',
                'overlapDcScm',
                'overlapDcCategory',
                'overlapDcBrand',
                'overlapDcGoods',
            ];
            $getData = gd_array_json_encode($getData, $jsonEncodeKeysByGroup);

            $checked['groupImageGb'][$getData['groupImageGb']] =
            $checked['groupMarkGb'][$getData['groupMarkGb']] =
            $checked['apprExclusionOfRatingFl'][$getData['apprExclusionOfRatingFl']] =
            $checked['fixedRatePrice'][$getData['fixedRatePrice']] = 'checked="checked"';

            $this->setData('mode', MemberGroup::MODE_REGISTER);
            $this->setData('data', $getData);
            $this->setData('checked', $checked);
            $this->setData('getBrandCnt', $getBrandData['cnt']);
            $this->setData('getBrandData', $getBrandData['data']);
            $this->setData('settleGbData', Util::getSettleGbData());
            $this->setData('settleGbDataCheck', Util::matchSettleGbDataToString('all'));
            $this->setData('fixedRateOptionData', Util::getFixedRateOptionData());
            $this->setData('fixedRatePriceData', Util::getFixedRatePriceData());
            $this->setData('fixedOrderTypeData', Util::getFixedOrderTypeData('brand'));
            $this->setData('fixedOrderTypeAllData', Util::getFixedOrderTypeData());
            $this->setData('dcOptionData', Util::getDcOptionData());

            $this->addScript(
                [
                    'member.js',
                    'jquery/jquery.multi_select_box.js',
                ]
            );
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
