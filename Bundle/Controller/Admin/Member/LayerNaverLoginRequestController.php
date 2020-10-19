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

use Component\Godo\GodoNaverServerApi;
use Component\Policy\NaverLoginPolicy;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;

/**
 * Class LayerNaverLoginRequestController
 * @package Bundle\Controller\Admin\Member
 */
class LayerNaverLoginRequestController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $naverApi = new GodoNaverServerApi();
            $naverPolicy = new NaverLoginPolicy();

            $this->setData('category', $naverPolicy->getCategory());
            $this->setData('terms', $naverApi->getNaverTerms());

            $policy = gd_policy(NaverLoginPolicy::KEY);
            $this->setData('useFl', gd_isset($policy['useFl'], 'f')); // first: 아무것도 안한 상태(최초)

            $this->setData('mode', 'regist');
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', \Request::getDirectoryUri() . '/' . \Request::getFileUri());
        } catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
