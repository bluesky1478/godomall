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

namespace Bundle\Component\Excel;


use Component\Mileage\Mileage;

/**
 * Class ExcelMileage
 * @package Bundle\Component\Excel
 * @author  yjwee
 */
class ExcelMileage
{
    public function formatMileage()
    {
        $reasons = [];
        $reasonCode = gd_code(Mileage::REASON_CODE_GROUP);
        foreach ($reasonCode as $index => $item) {
            $reasons[] = $index . ' : ' . $item;
        }
        $reasonStr = implode("<br style='mso-data-placement:same-cell;'>", $reasons);

        $fields = [
            [
                'dbName'   => 'memberMileage',
                'dbKey'    => 'memId',
                'excelKey' => 'mem_id',
                'text'     => __('아이디'),
                'sample'   => 'mileage1',
                'comment'  => __('대상 아이디'),
                'desc'     => __('지급/차감 할 아이디 입력'),
            ],
            [
                'dbName'   => 'memberMileage',
                'dbKey'    => 'handleMode',
                'excelKey' => 'handle_mode',
                'text'     => __('처리모드'),
                'sample'   => 'm',
                'comment'  => sprintf('m:%s, o:%s, b:%s, r:%s', __('회원'), __('주문'), __('게시판'), __('신규회원추천')),
                'desc'     => sprintf('%s (m:%s, o:%s, b:%s, r:%s)', __('지급/차감 처리모드 코드 입력'), __('회원'), __('주문'), __('게시판'), __('신규회원추천')),
            ],
            [
                'dbName'   => 'memberMileage',
                'dbKey'    => 'handleCd',
                'excelKey' => 'handle_cd',
                'text'     => '처리코드',
                'sample'   => 'test1',
                'comment'  => sprintf('%s, %s, %s', __('주문번호'), __('게시판코드'), __('신규회원추천아이디')),
                'desc'     => sprintf('%s(%s, %s, %s)', __('지급/차감 처리모드의 상세 코드'), __('주문번호'), __('게시판코드'), __('신규회원추천아이디')),
            ],
            [
                'dbName'   => 'memberMileage',
                'dbKey'    => 'handleNo',
                'excelKey' => 'handle_no',
                'text'     => '처리번호',
                'sample'   => '123',
                'comment'  => sprintf('%s, %s', __('상품번호'), __('게시물번호')),
                'desc'     => sprintf('%s(%s, %s)', __('처리코드의 상세 번호'), __('상품번호'), __('게시물번호')),
            ],
            [
                'dbName'   => 'memberMileage',
                'dbKey'    => 'mileage',
                'excelKey' => 'mileage',
                'text'     => __('마일리지'),
                'sample'   => '1000',
                'comment'  => __('마일리지'),
                'desc'     => __('지급/차감 마일리지 금액'),
            ],
            [
                'dbName'   => 'memberMileage',
                'dbKey'    => 'reasonCd',
                'excelKey' => 'reason_cd',
                'text'     => __('지급/차감 사유코드'),
                'sample'   => '01005011',
                'comment'  => $reasonStr,
                'desc'     => sprintf('%s<br />' . $reasonStr, __('지급/차감 사유코드 기타(01005011)를 제외한 코드는 미리 정의된 사유 자동입력')),
            ],
            [
                'dbName'   => 'memberMileage',
                'dbKey'    => 'contents',
                'excelKey' => 'contents',
                'text'     => __('지급/차감 사유'),
                'sample'   => __('설문조사 이벤트 참여'),
                'comment'  => __('사유'),
                'desc'     => __('지급/차감 사유코드 기타인 경우 입력되는 내용입니다.'),
            ],
        ];

        return $fields;
    }
}
