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

use App;
use Component\Excel\ExcelMemberConvert;
use Request;

/**
 * Class [관리자 모드] 회원 엑셀 처리 페이지
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class ExcelMemberPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $requestPostParams = Request::post()->all();
        /** @var \Bundle\Component\Excel\ExcelDataConvert $excel */
        $excel = App::load('\\Component\\Excel\\ExcelDataConvert');
        $excelMember = new ExcelMemberConvert();
        switch ($requestPostParams['mode']) {
            // 회원 엑셀 샘플 다운로드
            case 'excel_sample_down':
                $this->streamedDownload('회원샘플파일.xls');
                $excel->setExcelMemberSampleDown();
                exit();
                break;

            // 회원 엑셀 다운로드
            case 'excel_down':
                $this->streamedDownload('회원다운로드.xls');
                $excel->setExcelMemberDown($requestPostParams);
                exit();
                break;

            // 회원정보 수정 이벤트 참여내역 엑셀 다운로드
            case 'excel_modify_event_result_down':
                $this->streamedDownload('회원정보 수정 이벤트 참여내역 다운로드.xls');
                $excel->setExcelMemberModifyEventResultDown($requestPostParams);
                exit();
                break;

            // 회원 엑셀 업로드
            case 'excel_up':
                $this->streamedDownload('회원업로드결과.xls');
                $excelMember->upload();
                exit();
                break;
            // 회원가입 이벤트 로그 엑셀 다운로드
            case 'excel_simple_join_event':
                $title = $requestPostParams['eventType'] == 'order' ? '주문 간단 가입 내역':'가입 유도 푸시';
                $title .= ' ('.$requestPostParams['treatDate'][0].' ~ '.$requestPostParams['treatDate'][1].').xls';
                $this->streamedDownload($title);
                $excel->setExcelMemberSimpleJoinEventResultDown($requestPostParams);
                exit();
                break;
        }
    }
}
