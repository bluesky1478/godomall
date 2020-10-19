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
namespace Bundle\Controller\Admin\Service;

use Component\Mall\Mall;
/**
 * Class LivefeedConfigController
 * @package Bundle\Controller\Admin\Service
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class LivefeedConfigController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('service', 'serviceSetting', 'livefeedConfig');

        // --- 페이지 데이터
        try {
            // --- 모듈 호출
            $mall = new Mall();
            $db = \App::load('DB');

            $mallSno = gd_isset(\Request::get()->get('mallSno'), DEFAULT_MALL_NUMBER);

            if($mallSno == 1){
                $livefeedConfig = gd_policy('service.livefeed');
            }else{
                $arrBind = [];
                $db->strField = "data";
                $db->strWhere = "groupCode = 'service' AND code = 'livefeed' AND mallSno = ?";
                $db->bind_param_push($arrBind, 'i', $mallSno);

                $query = $db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CONFIG_GLOBAL . ' ' . implode(' ', $query);
                $res = $db->query_fetch($strSQL, $arrBind, false);

                if(empty($res) == false){
                    $data = json_decode($res['data'], true);
                    $livefeedConfig = $data;
                }
            }

            $mallList = $mall->getListByUseMall();
            if (count($mallList) > 1) {
                $this->setData('mallCnt', count($mallList));
                $this->setData('mallList', $mallList);

                if ($mallSno > 1) {
                    $defaultData = gd_policy('basic.info', DEFAULT_MALL_NUMBER);
                    foreach ($defaultData as $key => $value) {
                        if (in_array($key, Mall::GLOBAL_MALL_BASE_INFO) === true) $data[$key] = $value;
                    }

                    $disabled = ' disabled = "disabled"';
                    $readonly = ' readonly = "readonly"';
                    $this->setData('disabled', $disabled);
                    $this->setData('readonly', $readonly);
                }
            }

            // --- 기본값 설정
            gd_isset($livefeedConfig['livefeedUseType'],'n');
            gd_isset($livefeedConfig['livefeedDeviceType'],'all');
            gd_isset($livefeedConfig['livefeedServiceID']);
            gd_isset($livefeedConfig['livefeedAuthKey']);

            $checked['livefeedUseType'][$livefeedConfig['livefeedUseType']] =
            $checked['livefeedDeviceType'][$livefeedConfig['livefeedDeviceType']] = 'checked="checked"';

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->setData('data', $livefeedConfig);
        $this->setData('mallSno', $mallSno);
        $this->setData('checked', $checked);
    }
}
