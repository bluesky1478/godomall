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


use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Exception;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;

/**
 * 약관 관리 레이어
 *
 * @author hakyoung lee <haky2@godo.co.kr>
 */
class LayerInformListController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $mallSno = gd_isset(\Request::post()->get('mallSno'), 1);
            $policy = gd_policy('basic.agreement', $mallSno);
            $buyerInform = new BuyerInform();
            $item = \Request::post()->get('item');
            if ($item === 'agreement') {
                $informCd = BuyerInformCode::AGREEMENT;
                $informData['checked']['displayInformFl'][gd_isset($policy['displayAgreementFl'],'n')] = 'checked="checked"';
            } else {
                $informCd = BuyerInformCode::BASE_PRIVATE;
                $informData['checked']['displayInformFl'][gd_isset($policy['displayPrivateFl'],'n')] = 'checked="checked"';
            }
            $informData['data'] = $buyerInform->getInformData($informCd, $mallSno);
            if (ArrayUtils::dimension($informData['data']) < 2) {
                $informData['data'] = [$informData['data']];
            }
            $modDtCount = 0;
            foreach ($informData['data'] as $key => &$val) {
                $val['regDt'] = DateTimeUtils::dateFormat('Y-m-d', $val['regDt']);
                if (empty($val['modDt']) === false) {
                    $val['modDt'] = DateTimeUtils::dateFormat('Y-m-d', $val['modDt']);
                    $modDtCount++;
                }
                if ($val['displayShopFl'] === 'y') {
                    $informData['checked']['displayShopFl'][$val['informCd']] = 'checked="checked"';
                }
            }
            $informCount = count($informData['data']);
            if ($informCount > 4 || ($informCount > 3 && $modDtCount > 2)) {
                $this->setData('scrollFl', true);
            }

            $this->setData('mallSno', $mallSno);
            $this->setData('item', $item);
            $this->setData('data', $informData);
            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
