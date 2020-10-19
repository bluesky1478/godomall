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

namespace Bundle\Controller\Admin\Policy;


use Component\Mall\Mall;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertReloadException;
use Framework\Debug\Exception\LayerException;

/**
 * Class MallConfigPsController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MallConfigPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $message = \App::getInstance('message');
        $mallService = new Mall();
        $mode = $request->request()->get('mode', '');
        // 상점 기본 정보
        $mallInfo = gd_policy('basic.info');
        $logger->debug(__METHOD__, $request->request()->all());
        try {
            switch ($mode) {
                case 'modify':
                    if (empty($request->post()->get('connectDomain')) === false && empty($mallInfo['mallDomain']) === true) {
                        throw new LayerException(__('해외몰 연결도메인 추가를 위해서는 기본설정 > 기본정보설정 의 "쇼핑몰 도메인"을 먼저 입력하시기 바랍니다.'));
                    }
                    $mallService->modifyMall($request->post()->all());

                    // 보안서버 설정
                    $ssl = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
                    $ssl->setSslMallFl($request->post()->get('connectDomain'), $request->post()->get('domainFl'));

                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.replace("../policy/mall_config.php?domainFl=' . $request->post()->get('domainFl', 'us') . '");');
                    break;
                case 'validateConnectDomain':
                    if (empty($mallInfo['mallDomain']) === true) {
                        throw new Exception(__('해외몰 연결도메인 추가를 위해서는 기본설정 > 기본정보설정 의 "쇼핑몰 도메인"을 먼저 입력하시기 바랍니다.'));
                    }
                    $mallService->validateConnectDomain($request->post()->get('connectDomain'));
                    $this->json(
                        [
                            'result' => true,
                        ]
                    );
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if ($request->isAjax()) {
                $this->json(
                    [
                        'result'  => false,
                        'message' => $e->getMessage(),
                    ]
                );
            } else {
                throw new LayerException($e->getMessage(), $e->getCode(), $e, 'top.location.replace("../policy/mall_config.php?domainFl=' . $request->post()->get('domainFl', 'us') . '");');
            }
        }
    }
}
