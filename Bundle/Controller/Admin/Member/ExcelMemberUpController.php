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

use Component\Excel\ExcelMember;


/**
 * Class [관리자 모드] 회원 엑셀 업로드 페이지
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class ExcelMemberUpController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        $this->callMenu('member', 'member', 'excelUp');

        $excel = new ExcelMember();
        $excelField = $excel->formatMember();

        $this->setData('excelField', $excelField);
    }
}
