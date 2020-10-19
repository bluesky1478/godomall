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

use Component\Godo\GodoNhnServerApi;
use Component\Nhn\Paycosearch;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Globals;

/**
 * Class LayerPaycosearchRegistController
 * @package Bundle\Controller\Admin\Policy
 * @author  yoonar
 */
class LayerPaycosearchRegistController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $paycosearch = new Paycosearch();
            $paycosearchApi = new GodoNhnServerApi();

            $paycosearchConfig = $paycosearch->getConfig();
            $domain = Globals::get('gMall')['mallDomain'];
            if(!$paycosearchConfig['searchShopName']) $paycosearchConfig['searchShopName'] = $domain;
            $domainList = $paycosearchApi->request('paycosearchUrlList', '');
            $terms = $paycosearchApi->request('paycosearchTermUrl', '');

            if($domainList) {
                $this->setData('domainList', $domainList);
                $this->setData('domain', $domain);
                $this->setData('terms', $terms);
            } else {
                throw new AlertOnlyException('페이코 서치 설정 중 오류가 발생하였습니다. 잠시 후 다시 시도해주세요.');
                Logger::channel('paycosearch')->info('도메인 리스트 생성 오류', ['PaycosearchRegistController', $domainList]);
            }

            if(gd_isset($paycosearchConfig['searchUse'])) {
                $mode = 'insertAgainSearchPopup';
            } else {
                $mode = 'insertSearchPopup';
            }
            $this->setData('paycosearchConfig', $paycosearchConfig);

            $this->setData('mode', $mode);
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', \Request::getDirectoryUri() . '/' . \Request::getFileUri());
        } catch (Exception $e) {
            throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
