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

namespace Bundle\Controller\Admin\Promotion;

use Exception;

class EventSaleConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('promotion', 'eventSale', 'config');

        try {
            $data = gd_policy('promotion.event');

            $event = \App::load('\\Component\\Goods\\GoodsAdmin');

            if(count($data['otherEventExtraNo']) > 0){
                foreach($data['otherEventExtraNo'] as $key => $otherEventNo){
                    \Request::get()->set('sno', $otherEventNo);
                    $getData = array();
                    $getData = $event->getAdminListDisplayTheme('event');
                    $data['otherEventExtraData'][$key]['eventNo'] = $otherEventNo;
                    $data['otherEventExtraData'][$key]['eventName'] = $getData['data'][0]['themeNm'];
                }
            }
            if(count($data['otherEventNo']) > 0){
                foreach($data['otherEventNo'] as $key => $otherEventNo){
                    \Request::get()->set('sno', $otherEventNo);
                    $getData = array();
                    $getData = $event->getAdminListDisplayTheme('event');
                    $data['otherEventData'][$key]['eventNo'] = $otherEventNo;
                    $data['otherEventData'][$key]['eventName'] = $getData['data'][0]['themeNm'];
                }
            }

            gd_isset($data['otherEventUseFl'], 'n');
            gd_isset($data['otherEventDefaultText'], '');
            gd_isset($data['otherEventDisplayFl'], 'n');
            gd_isset($data['otherEventBottomFirstFl'], 'n');
            gd_isset($data['otherEventSortType'], 'auto');
            gd_isset($data['otherEventSortTypeTa'], 'regdt desc');
            gd_isset($data['otherEventSortTypeTb'], 'top');
            $data['otherEventSortTypeTaList'] = [
                'regdt desc' => '최근 등록순',
                'themeNm asc' => '가나다순',
                'displayEndDate asc' => '종료 임박순',
            ];
            $data['otherEventSortTypeTbList'] = [
                'top' => '신규 추가 기획전 위로',
                'bottom' => '신규 추가 기획전 아래로',
            ];

            $checked['otherEventUseFl'][$data['otherEventUseFl']] = $checked['otherEventDisplayFl'][$data['otherEventDisplayFl']] = $checked['otherEventBottomFirstFl'][$data['otherEventBottomFirstFl']] = $checked['otherEventSortType'][$data['otherEventSortType']] = 'checked="checked"';

            $this->setData('data', $data);
            $this->setData('checked', $checked);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
