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

use Bundle\Component\Godo\GodoAutoExchangeApi;
use Component\Database\DBTableField;
use Component\ExchangeRate\ExchangeRate;
use Framework\Debug\Exception\LayerException;
use Request;

/**
 * Class ExchangeRateConfigController
 * @package Bundle\GlobalController\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class ExchangeRateConfigController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws LayerException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'multiMall', 'exchangeRateConfig');

        // --- 관리자 데이터
        try {
            $exchangeRate = new ExchangeRate();

            // 자동환율데이터가 없는 경우를 대비해 자동으로 insert 처리
            $exchangeRate->manualSetExchangeRateAuto();

            // 환율 설정 데이터
            $exchangeRateConfig = $exchangeRate->getExchangeRateConfig();
            if (count($exchangeRateConfig) < 1) {
                DBTableField::setDefaultData('tableExchangeRateConfig', $exchangeRateConfig);
            }
            foreach ($exchangeRate->globalCurrencyData as $currencyKey => $currencyVal) {
                $checked['exchangeRateConfig' . $currencyVal['globalCurrencyString'] . 'Type'][$exchangeRateConfig['exchangeRateConfig' . $currencyVal['globalCurrencyString'] . 'Type']] = 'checked="checked"';
                $selected['exchangeRateConfig' . $currencyVal['globalCurrencyString'] . 'Type'][$exchangeRateConfig['exchangeRateConfig' . $currencyVal['globalCurrencyString'] . 'Type']] = 'selected="selected"';
            }

            // 자동 환율 데이터
            $exchangeRateAuto = $exchangeRate->getExchangeRateAuto();
            if (count($exchangeRateAuto) < 1) {
                DBTableField::setDefaultData('tableExchangeRateAuto', $exchangeRateAuto);
            }

            // 실 적용 최종 환율 데이터
            $exchangeRateReal = $exchangeRate->getExchangeRate();
            if (count($exchangeRateReal) < 1) {
                DBTableField::setDefaultData('tableExchangeRate', $exchangeRateReal);
            }

            // 환율 log 데이터
            $getValue = Request::get()->toArray();
            $exchangeRateLog = $exchangeRate->getExchangeRateLog($getValue);
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $this->setData('globalCurrencyData', $exchangeRate->globalCurrencyData);
            $this->setData('exchangeRateConfig', $exchangeRateConfig);
            $this->setData('exchangeRateAuto', $exchangeRateAuto);
            $this->setData('exchangeRateReal', $exchangeRateReal);
            $this->setData('exchangeRateLog', $exchangeRateLog);
            $this->setData('checked', $checked);
            $this->setData('selected', $selected);
            $this->setData('page', $page);
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
