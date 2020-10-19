<form id="frm" action="../member/hackout_ps.php" method="post">
    <input type="hidden" name="mode" value="modify"/>
    <input type="hidden" name="sno" value="<?= gd_isset($data['sno']) ?>"/>
    <input type="hidden" name="memNo" value="<?= gd_isset($data['memNo']) ?>"/>
    <input type="hidden" name="managerId" value="<?= gd_isset($data['managerId']) ?>"/>
    <input type="hidden" name="hackType" value="<?= gd_isset($_hackType[$data['hackType']]) ?>"/>

    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location) ?></h3>
        <input type="submit" value="저장" class="btn btn-red">
    </div>

    <div class="table-title gd-help-manual">탈퇴내역 상세정보</div>
    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-xl"/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>탈퇴유형</th>
                <td><?= $_hackType[$data['hackType']]; ?></td>
                <th>탈퇴일</th>
                <td>
                    <span class="font-num"><?= $data['hackDt']; ?></span>
                </td>
            </tr>
            <tr>
                <th>탈퇴처리 IP</th>
                <td>
                    <span
                        class="font-num"><?= $data['hackType'] == 'directManager' ? $data['managerIp'] : $data['regIp'] ?></span>
                </td>
                <th>처리자</th>
                <td>
                    <span
                        class="font-eng"><?= $data['hackType'] == 'directManager' ? $data['managerNm'] . '<br/>(' . $data['managerId'] . ')' : '-' ?><?=$data['deleteText']?></span>
                </td>
            </tr>
            <tr>
                <th>재가입여부</th>
                <td class="input_area" colspan="3">
                    <span class="font-eng"><?= $data['rejoinFl'] == 'y' ? '가능' : '불가능' ?></span>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">탈퇴자정보</div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>아이디</th>
                <td>
                    <strong><?= gd_isset($data['memId']); ?></strong>
                </td>
            </tr>
            <tr>
                <th>불편사항</th>
                <td>
                    <ol style="line-height:18px;">
                        <?php if (isset($data['reasonCd']) && is_array($data['reasonCd'])) {
                            foreach ($data['reasonCd'] as $k => $v) { ?>
                                <li style="margin-left:20px; list-style:disc;"><?= $reasonCds[$v]; ?></li>
                            <?php }
                        } ?>
                    </ol>
                </td>
            </tr>
            <tr>
                <th>충고 말씀</th>
                <td>
                    <span title="메모를 작성해 주세요!">
				<textarea name="reasonDesc" rows="5" cols=""
                          class="form-control width90p"><?= gd_isset($data['reasonDesc']); ?></textarea>
                    </span>
                    <span id="memoMsg" class="input_error_msg"></span>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        관리자 메모
    </div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>관리자 메모</th>
                <td>
                    <span title="관리자 메모 작성하실 수 있습니다.">
				<textarea name="adminMemo" rows="5" cols=""
                          class="form-control width90p"><?= gd_isset($data['adminMemo']); ?></textarea>
                    </span>
                </td>
            </tr>
        </table>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 회원탈퇴정보저장
        $("#frm").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
                return false;
            },
            rules: {},
            messages: {}
        });
    });
    //-->
</script>
