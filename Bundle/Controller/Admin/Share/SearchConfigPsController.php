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
namespace Bundle\Controller\Admin\Share;

use Component\Member\ManagerSearchConfig;
use Framework\Debug\Exception\LayerException;
use Request;
use Exception;

/**
 * 운영자 검색설정값 설정
 *
 * @author <cjb3333@godo.co.kr>
 */
class SearchConfigPsController extends \Controller\Admin\Controller
{
    /**
     * @todo 기간검색 설정을 searchPeriod로 통일해야 함 (회원/주문쪽)
     * @var array 검색폼 데이터에서 예외처리할 키를 정의하세요.
     */
    private $_exceptFieldKey = [
        'keyword',
        'delFl',
        'searchFl',
        'treatDate',
        'searchDate',
        'periodFl',
        'searchPeriod',
        'regDtPeriod',
        'view',
        'userHandleViewFl',
    ];

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 리퀘스트 데이터
            $getValue = Request::get()->toArray();

            // 검색설정 관리 모듈 호출
            $searchConf = new ManagerSearchConfig();

            switch ($getValue['mode']) {
                case 'changeSearchGrid' :
                    $searchConf->setIsOrderSearchMultiGrid($getValue);
                    break;
                default :
                    $searchConf->setSearchConfig($getValue, $this->_exceptFieldKey);
                    $addScript = 'top.location.href="' . $getValue['applyPath'] . '"';

                    throw new LayerException(__('설정값이 저장 되었습니다.'), null, null, gd_isset($addScript), 1000, true);
                    break;
            }
        } catch (Exception $e) {
            throw $e;
        }
        exit;
    }
}
