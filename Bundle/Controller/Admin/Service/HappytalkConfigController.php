<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Service;

/**
 * 카카오 상담톡 설정
 *
 * @package Bundle\Controller\Admin\Service;
 * @author  JaeDoo Lee <dlwoen9@godo.co.kr>
 */
class HappytalkConfigController extends \Controller\Admin\Controller
{
    public function index() {
        // --- 메뉴 설정
        $this->callMenu('service', 'serviceSetting', 'happytalkConfig');

        try {
            // --- 상담톡 설정
            $happytalkConfig = gd_policy('service.happytalk');
            $kakaoPlusIdConfig = gd_policy('kakaoAlrim.config');

            gd_isset($happytalkConfig['useHappytalkFl'], 'n');
            gd_isset($happytalkConfig['happytalkDeviceType'], 'all');

            $checked['useHappytalkFl'][$happytalkConfig['useHappytalkFl']] = 'checked="checked"';
            $checked['happytalkDeviceType'][$happytalkConfig['happytalkDeviceType']] = 'checked="checked"';

            if ($happytalkConfig['useHappytalkFl'] !== 'y') {
                $readOnly = 'readonly="readonly"';
                $disabled = 'disabled="disabled"';
            }

            $this->setData('data', gd_isset($happytalkConfig));
            $this->setData('checked', gd_isset($checked));
            $this->setData('readOnly', gd_isset($readOnly));
            $this->setData('disabled', gd_isset($disabled));
            $this->setData('kakaoPlusId', gd_isset($kakaoPlusIdConfig['plusId']));
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage(), $e->getCode(), $e);
        }

    }
}
