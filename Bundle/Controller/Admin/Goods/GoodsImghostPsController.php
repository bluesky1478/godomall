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
namespace Bundle\Controller\Admin\Goods;

use App;
use Exception;
use Framework\Debug\Exception\HttpException;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Encryptor;
use Session;
use Globals;
use Request;

class GoodsImghostPsController extends \Controller\Admin\Controller
{
    /**
     * 이미지호스팅일괄전환 처리
     *
     * @author sunny
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */
    public function index()
    {
        // --- POST 값 처리
        $aPost = Request::request()->toArray();

        $imageHostingReplace = \App::load('\\Component\\Goods\\ImageHostingReplace');

        // --- 모듈 호출
        try {
            switch ($aPost['mode']) {
                // FTP접속검증
                case 'ftpVerify':
                    $imageHostingReplace->checkUseStorage($aPost);
                    $this->json(['result' => 'ok']);
                    break;

                // 전환
                case 'putReplace':
                    $aPost['ftpPw'] = Encryptor::encrypt($aPost['ftpPw']);

                    $resultCount = $imageHostingReplace->doReplace($aPost);

                    $this->json(['result' => 'ok', 'resultCount' => $resultCount]);

                    break;
            }
        } catch (\Exception $e) {
            $this->json(['result' => 'fail','errMsg' => $e->getMessage()]);
        }
    }
}
