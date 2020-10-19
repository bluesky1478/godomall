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
namespace Bundle\Controller\Front\Service;
use Component\Board\Board;
use Framework\Debug\Exception\AlertBackException;

/**
 * 회사소개
 *
 * @author    sunny
 * @version   1.0
 * @since     1.0
 * @copyright Copyright (c), Godosoft
 */
class CooperationController extends \Controller\Front\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
//        if (gd_is_login() === false) {
//            throw new AlertBackException('회원전용 서비스입니다.');
//        }

        $this->setData('bdId', Board::BASIC_COOPERATION_ID);
    }
}
