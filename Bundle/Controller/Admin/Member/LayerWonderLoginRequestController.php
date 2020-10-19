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

namespace Bundle\Controller\Admin\Member;

use Component\Godo\GodoWonderServerApi;
use Component\Policy\WonderLoginPolicy;
use Component\Policy\Policy;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;

/**
 * Class LayerWonderLoginRequestController
 * @package Bundle\Controller\Admin\Member
 */
class LayerWonderLoginRequestController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $wonderApi = new GodoWonderServerApi();
            $wonderPolicy = new WonderLoginPolicy();
            $mode = gd_isset(\Request::get()->get('mode'), 'regist');

            $policy = gd_policy(WonderLoginPolicy::KEY);
            $this->setData('useFl', gd_isset($policy['useFl'], 'f')); // first: 아무것도 안한 상태(최초)

            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', \Request::getDirectoryUri() . '/' . \Request::getFileUri());

            if ($mode == 'regist') {
                $mallSno = gd_isset(\Request::get()->get('mallSno'), DEFAULT_MALL_NUMBER);
                $policy = new Policy();
                $baseData = $policy->getValue('basic.info', $mallSno);
                $baseInfo = [
                    'mallNm' => $baseData['mallNm'],
                    'centerEmail' => $baseData['email'],
                    'companyNm' => $baseData['companyNm'],
                    'ceoNm' => $baseData['ceoNm'],
                    'businessNo' => str_replace("-", "", $baseData['businessNo']),
                    'redirectUri' => implode($wonderApi->getRedirectUriList(), PHP_EOL),
                ];
                $this->setData('terms', $wonderApi->getTerms('godo'));
            } else {
                $baseInfo = [
                    'mallNm' => $policy['serviceName'],
                    'redirectUri' => str_replace(',', PHP_EOL, $policy['redirectUri']),
                ];
                $this->setData('secureRedirectUri', @implode('<br />', $wonderApi->getRedirectUriList('secure')));
            }
            $this->setData('baseInfo', $baseInfo);
            $this->setData('mode', $mode);

        } catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
