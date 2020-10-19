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
namespace Bundle\Controller\Admin\Promotion;

use Component\Promotion\ShortUrl;
use Framework\Debug\Exception\LayerException;
use Component\Excel\ExcelVisitStatisticsConvert;
use Request;

/**
 * Class ShortUrlPsController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class ShortUrlPsController extends \Controller\Admin\Controller
{

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // --- 페이지 데이터
        $requestData = Request::request()->toArray();

        // ShortUrl 객체 생성
        $shortUrl = new ShortUrl();

        // 설치여부 판독
        if (!$shortUrl->getIsInstalled()) {
            throw new LayerException(__('플러스샵 설치가 필요합니다.'));
        }

        // 액션에 따른 분기 처리
        switch ($requestData['mode']) {
            // 단축주소 등록하기
            case 'registShortUrl':
                try {
                    if ($shortUrl->addUrl($requestData['longUrl'], $requestData['description'])) {
                        throw new LayerException(__('등록이 완료되었습니다.'));
                    } else {
                        throw new LayerException(__('단축URL 서버 사용량때문에 등록에 실패했습니다. 잠시 후 다시 시도해주세요.'));
                    }
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;

            case 'deleteShortUrl':
                try {
                    foreach ($requestData['chk'] as $sno) {
                        $shortUrl->deleteUrl($sno);
                    }
                    throw new LayerException(__('삭제가 완료되었습니다.'));

                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }

            case 'shorturlExcelDownload':
                if ($requestData['excel_name'] == '') {
                    throw new Exception(__('요청을 찾을 수 없습니다.'));
                    break;
                }

                $this->streamedDownload($requestData['excel_name'] . '.xls');
                $excel = new ExcelVisitStatisticsConvert();
                $excel->setExcelDownByJoinData(urldecode($requestData['data']));
                exit();
                break;
        }

        exit;
    }
}
