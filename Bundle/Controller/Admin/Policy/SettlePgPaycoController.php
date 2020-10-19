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

/**
 * 페이코 설정/관리
 * @author Lee Hoon <akari2414@godo.co.kr>
 */
class SettlePgPaycoController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'settle', 'payco');

        // --- 페이지 데이터
        try {
            $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
            $gift = \App::load('\\Component\\Gift\\Gift');

            // 페이코 정보
            $data = gd_policy('pg.payco');

            // 페이코 신청 여부
            $paycoApproval = true;
            if(empty($data)) {
                $paycoApproval = false;
            }
            if (empty($data['paycoSellerKey']) === true || empty($data['paycoCpId']) === true) {
                $paycoApproval = false;
            }

            // 기본 값 처리
            gd_isset($data['auto']);
            gd_isset($data['useType'], 'N');
            gd_isset($data['testYn'], 'Y');
            gd_isset($data['useYn'], 'all');
            gd_isset($data['useEventPopupYn'], 'n');
            gd_isset($data['eventPopupTop'], 0);
            gd_isset($data['eventPopupLeft'], 0);
            gd_isset($data['button_checkout'], 'A');
            gd_isset($data['button_checkoutDetail'], 'A1');
            gd_isset($data['button_easypay'], 'A1');
            gd_isset($data['individualCustomNoInputUseYn'], 'n');

            if ($data['exceptGoods']) {
                $data['exceptGoodsNo'] = $gift->viewGoodsData(implode(INT_DIVISION, $data['exceptGoods']));
            }

            if($data['exceptCategory']) {
                $data['exceptCateCd'] = $gift->viewCategoryData(implode(INT_DIVISION, $data['exceptCategory']));
            }

            if($data['exceptBrand']) {
                $data['exceptBrandCd'] = $gift->viewCategoryData(implode(INT_DIVISION, $data['exceptBrand']), 'brand');
            }

            $checked = [];
            $checked['useType'][$data['useType']] = 'checked="checked"';
            $checked['testYn'][$data['testYn']] = 'checked="checked"';
            $checked['useYn'][$data['useYn']] = 'checked="checked"';
            $checked['useEventPopupYn'][$data['useEventPopupYn']] = 'checked="checked"';
            $checked['button_checkout'][$data['button_checkout']] = 'checked="checked"';
            $checked['button_checkoutDetail'][$data['button_checkoutDetail']] = 'checked="checked"';
            $checked['button_easypay'][$data['button_easypay']] = 'checked="checked"';
            $checked['individualCustomNoInputUseYn'][$data['individualCustomNoInputUseYn']] = 'checked="checked"';
            $checked['fc'][$data['settleKind']['fc']]=
            $checked['fb'][$data['settleKind']['fb']]=
            $checked['fv'][$data['settleKind']['fv']]=
            $checked['fh'][$data['settleKind']['fh']]=
            $checked['fp'][$data['settleKind']['fp']]= 'checked="checked"';

            // PG 중앙화에 따른 결제 수단 제한
            $disabled = [];
            if (isset($data['pgAutoSetting']) === true) {
                $disabled['fc'][gd_isset($data['disabledSettleKind']['fc'], 'n')]=
                $disabled['fb'][gd_isset($data['disabledSettleKind']['fb'], 'n')]=
                $disabled['fv'][gd_isset($data['disabledSettleKind']['fv'], 'n')]=
                $disabled['fh'][gd_isset($data['disabledSettleKind']['fh'], 'n')]=
                $disabled['fp'][gd_isset($data['disabledSettleKind']['fp'], 'n')]= 'disabled="disabled"';
            }

            $image['A1'] = $payco->getPaycoAdminButtonImageUrl('A1', 'png');
            $image['A2'] = $payco->getPaycoAdminButtonImageUrl('A2', 'png');
            $image['A3'] = $payco->getPaycoAdminButtonImageUrl('A3', 'png');
            $image['A4'] = $payco->getPaycoAdminButtonImageUrl('A4', 'png');
            $image['A5'] = $payco->getPaycoAdminButtonImageUrl('A5', 'png');
            $image['A6'] = $payco->getPaycoAdminButtonImageUrl('A6', 'png');

            $image['B1'] = $payco->getPaycoAdminButtonImageUrl('B1', 'png');
            $image['B2'] = $payco->getPaycoAdminButtonImageUrl('B2', 'png');
            $image['B3'] = $payco->getPaycoAdminButtonImageUrl('B3', 'png');
            $image['B4'] = $payco->getPaycoAdminButtonImageUrl('B4', 'png');
            $image['B5'] = $payco->getPaycoAdminButtonImageUrl('B5', 'png');
            $image['B6'] = $payco->getPaycoAdminButtonImageUrl('B6', 'png');

            $image['C1'] = $payco->getPaycoAdminButtonImageUrl('C1', 'png');
            $image['C2'] = $payco->getPaycoAdminButtonImageUrl('C2', 'png');
            $image['C3'] = $payco->getPaycoAdminButtonImageUrl('C3', 'png');
            $image['C4'] = $payco->getPaycoAdminButtonImageUrl('C4', 'png');
            $image['C5'] = $payco->getPaycoAdminButtonImageUrl('C5', 'png');
            $image['C6'] = $payco->getPaycoAdminButtonImageUrl('C6', 'png');

            $image['easypay_A1'] = $payco->getPaycoAdminButtonImageUrl('easypay_A1', 'png');
            $image['easypay_A2'] = $payco->getPaycoAdminButtonImageUrl('easypay_A2', 'png');

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->addCss([
            'payco.css',
        ]);
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
            'payco.js',
        ]);

        $this->setData('data', $data);
        $this->setData('paycoApproval', $paycoApproval);
        $this->setData('image', $image);
        $this->setData('checked', $checked);
        $this->setData('disabled', $disabled);
    }
}
