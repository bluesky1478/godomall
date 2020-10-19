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

namespace Bundle\Component\Excel;

/**
 * Class ExcelSms
 * @package Bundle\Component\Excel
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class ExcelSms
{
    public function formatSms()
    {
        $fields = [
            [
                'dbKey'    => 'name',
                'excelKey' => 'name',
                'text'     => '이름',
                'comment'  => '',
                'sample'   => '홍길동',
                'desc'     => '불필요한 경우 이름 필드 열을 삭제하여 주시기 바랍니다.<br/>(삭제하지 않고 공란으로 입력 시 해당 번호는 업로드할 수 없음)<br/>- 치환코드 {name}으로 발송 내용에 이름 입력 가능',
            ],
            [
                'dbKey'    => 'cellPhone',
                'excelKey' => 'cellPhone',
                'text'     => '휴대폰번호(필수)',
                'comment'  => '',
                'sample'   => '010-1234-1234',
                'desc'     => '형식) xxx-xxxx-xxxx or xxxxxxxxxxx<br/>- 이름이 불필요한 경우 이름 필드(A열)를 삭제하고 휴대폰 번호만 입력<br/>- 휴대폰번호만 업로드한 경우, 이름 치환코드{name}을 사용하실 수 없습니다.',
            ],
        ];

        return $fields;
    }
}
