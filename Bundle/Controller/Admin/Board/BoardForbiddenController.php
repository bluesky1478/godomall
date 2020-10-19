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
namespace Bundle\Controller\Admin\Board;

use Framework\Debug\Exception\AlertBackException;
use Globals;
use Framework\Debug\Exception\Except;

/**
 *
 * @author LeeNamJu
 */
class BoardForbiddenController extends \Controller\Admin\Controller
{
    /**
     * Description
     */
    public function index()
    {

        /**
         * 게시판 금칙어 관리.
         *
         * @author sj
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출

        // --- 메뉴 설정
        $this->callMenu('board', 'board', 'forbidden');

        // --- 페이지 데이터
        try {
            $forbidden = gd_policy('board.forbidden');
            gd_isset($forbidden['word']);
            $forbidden = str_replace(STR_DIVISION, ',', $forbidden['word']);
        } catch (Except $e) {
            throw new AlertBackException($e->ectMessage);
        }

        // --- 관리자 디자인 템플릿
        $this->setData('forbidden', $forbidden);

        unset($forbidden);
    }
}
