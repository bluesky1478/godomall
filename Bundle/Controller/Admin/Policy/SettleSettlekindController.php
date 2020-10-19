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
namespace Bundle\Controller\Admin\Policy;

use Globals;
use App;

/**
 * 결제 수단 설정 페이지
 *
 * @author Jont-tae Ahn <qnibus@godo.co.kr>
 */
class SettleSettlekindController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        try {
            // 메뉴 설정
            $this->callMenu('policy', 'settle', 'settleKind');

            // 결제수단 정책 불러오기
            $policy = App::load(\Component\Policy\Policy::class);
            $data = $policy->getDefaultSettleKind();

            // PG 설정에 따른 결제 수단 체크
            $disabled = [];
            $pgConf = gd_pgs();
            $mpgConf = gd_mpgs();

            // 체크할 결제 수단
            $checkSettleKind = ['pc', 'pb', 'pv', 'ph', 'ec', 'eb', 'ev'];

            // PG 결제 사용 여부
            $pgConfFl = false;

            // PG ID 체크
            if (empty($pgConf['pgId']) === true) {
                // 사용 여부 체크
                foreach ($checkSettleKind as $value) {
                    $data[$value]['useFl'] = 'n';
                }

                // disabled 처리
                foreach ($checkSettleKind as $value) {
                    $disabled[$value]['y'] = 'disabled="disabled"';
                }
            } else {
                $pgConfFl = true;
            }

            // 휴대폰 결제 사용 여부
            $mobilePgConfFl = false;

            // 휴대폰 결제 설정에 따른 체크
            if (empty($mpgConf['pgId']) === false) {
                // 사용 여부 체크
                if ($mpgConf['useFl'] === 'y') {
                    $data['ph']['useFl'] = 'y';
                } else {
                    $data['ph']['useFl'] = 'n';
                }
                $disabled['ph']['y'] = $disabled['ph']['n'] = 'disabled="disabled"';
                $mobilePgConfFl = true;
            }

            // PG 중앙화에 따른 결제 수단 체크
            if (isset($pgConf['pgAutoSetting']) === true) {
                if ($pgConf['pgAutoSetting'] === 'y') {
                    // disabled 처리
                    foreach ($checkSettleKind as $value) {
                        if (gd_isset($pgConf['disabledSettleKind'][$value])) {
                            $disabled[$value]['y'] = 'disabled="disabled"';
                        }
                    }
                }
            }

            // checked 처리
            $checked = [];
            foreach ($checkSettleKind as $value) {
                $checked[$value][$data[$value]['useFl']] = 'checked="checked"';
            }
            $checked['gb'][$data['gb']['useFl']] = 'checked="checked"';

            $this->setData('pgConfFl', $pgConfFl);
            $this->setData('mobilePgConfFl', $mobilePgConfFl);
            $this->setData('data', $data);
            $this->setData('checked', $checked);
            $this->setData('disabled', $disabled);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
