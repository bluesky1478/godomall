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

namespace Bundle\Controller\Admin\Promotion;

use Exception;
use Framework\Debug\Exception\LayerException;

/**
 * Class AttendancePsController
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 */
class AttendancePsController extends \Controller\Admin\Controller
{
    const MODE_INSERT = 'insert';
    const MODE_MODIFY = 'modify';
    const MODE_DELETE = 'delete';
    const MODE_INSERT_BENEFIT = 'insertBenefit';
    const MODE_DOWNLOAD_DETAIL_EXCEL = 'downloadDetailExcel';

    private $exceptScript;

    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            /** @var \Bundle\Component\Attendance\Attendance $attendance */
            $attendance = \App::load('\\Component\\Attendance\\Attendance');

            $mode = \Request::post()->get('mode', \Request::get()->get('mode'));

            switch ($mode) {
                case self::MODE_INSERT:
                    $this->exceptScript = 'return false';
                    $attendance->register(array_merge(\Request::post()->all(), \Request::files()->all()));
                    $this->layer(__('등록되었습니다.'), 'top.location.href="../promotion/attendance_list.php";');
                    break;
                case self::MODE_MODIFY:
                    $this->exceptScript = 'return false';
                    $attendance->modify(array_merge(\Request::post()->all(), \Request::files()->all()));
                    $this->layer(__('수정되었습니다.'), 'top.location.href="../promotion/attendance_list.php";');
                    break;
                case self::MODE_DELETE:
                    $attendance->delete(\Request::post()->get('chk'));
                    $this->json(__('삭제되었습니다.'));
                    break;
                case self::MODE_INSERT_BENEFIT:
                    \DB::begin_tran();
                    /** @var \Bundle\Component\Attendance\AttendanceBenefit $benefit */
                    $benefit = \App::load('\\Component\\Attendance\\AttendanceBenefit');
                    try {
                        $benefit->setRequest();
                        $benefit->benefitByManual();
                    } catch (Exception $e) {
                        \DB::rollback();
                        $this->json($e->getMessage());
                    }
                    \DB::commit();
                    $this->json(sprintf(__('%d명의 회원에게 출석체크 이벤트 혜택이 지급되었습니다.'), $benefit->getResultStorage()->get('success', 0)));
                    break;
                case self::MODE_DOWNLOAD_DETAIL_EXCEL:

                    break;
                default:
                    throw new Exception(__("요청을 찾을 수 없습니다."));
                    break;
            }
        } catch (Exception $e) {
            if (\Request::isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new LayerException($e->getMessage(), $e->getCode(), $e, $this->exceptScript);
            }
        }
    }
}
