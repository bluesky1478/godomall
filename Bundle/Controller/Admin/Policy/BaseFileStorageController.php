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

use Framework\Debug\Exception\Except;
use Framework\StaticProxy\Proxy\Session;
use Globals;

/**
 * Class BaseFileStorageController
 *
 * @package Controller\Admin\Policy
 * @author  Jong-tae Ahn <lnjts@godo.co.kr>
 */
class BaseFileStorageController extends \Controller\Admin\Controller
{
    /**
     * 화일 저장소 관리 페이지
     * [관리자 모드] 화일 저장소 관리 페이지
     *
     * @author lnjts
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @internal param array $get
     * @internal param array $post
     * @internal param array $files
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'basic', 'storage');

        // --- 화일 저장소 정보
        try {
            $data = gd_policy('basic.storage');
        } catch (\Exception $e) {
            debug($e->getMessage());
        }

        // 파일 저장소 관리 -> 상품 등록 시 기본 설정값 없으면 - config값 수정까지 필요없음. 설정안하면
        // default가 기본경로.
        if (empty($data['storageDefault']) === true) {
            $data['storageDefault'] = array('imageStorage0' => array('goods'));
        }

        $this->setData('data', $data);
    }
}
