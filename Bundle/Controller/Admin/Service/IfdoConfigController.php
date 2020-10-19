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

/**
 * Class IfdoConfigController
 * @package Bundle\Controller\Admin\Service
 * @author  choisueun <cseun555@godo.co.kr>
 */
class IfdoConfigController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('service', 'serviceSetting', 'ifdoConfig');

        // --- 페이지 데이터
        try {
            $ifdoConfig = gd_policy('service.ifdo');

            // --- 기본값 설정
            gd_isset($ifdoConfig['ifdoUseType'],'n');
            gd_isset($ifdoConfig['ifdoServiceCode']);

            $checked['ifdoUseType'][$ifdoConfig['ifdoUseType']] = 'checked="checked"';

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->setData('data', $ifdoConfig);
        $this->setData('checked', $checked);
    }
}
