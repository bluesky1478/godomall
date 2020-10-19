<div>
    <div class="manager-list-head">
        <table class="table table-rows table-fixed mgb0">
            <thead>
            <tr>
                <th class="width10p center"><input type="checkbox" id="chk_all" class="js-checkall" data-target-name="manage_sno"/></th>
                <th class="width10p center">번호</th>
                <th class="width20p center">공급사 구분</th>
                <th class="center">아이디/닉네임</th>
                <th class="width20p center">이름</th>
                <th class="width18p center">개별수정</th>
            </tr>
            </thead>
        </table>
    </div>
    <div class="manager-list-body mgb15">
        <table class="table table-rows table-fixed mgb0">
            <colgroup>
                <col class="width10p"/>
                <col class="width10p"/>
                <col class="width20p"/>
                <col/>
                <col class="width20p"/>
                <col class="width18p"/>
            </colgroup>
            <tbody>
            <?php
            if (gd_isset($data) && is_array($data)) {
                foreach ($data as $key => $val) {
                    $superText = '';
                    if ($val['isSuper'] == 'y') {
                        $superText = ($val['sno'] == 1) ? '<br/><span class="text-blue">(최고운영자)</span>' : '<br/><span class="text-blue">(대표운영자)</span>';
                    }
                    ?>
                    <tr data-sno="<?= $val['sno']; ?>" data-issuper="<?= $val['isSuper']; ?>">
                        <td class="center">
                            <input type="checkbox" name="manage_sno[]" value="<?= $val['sno']; ?>" />
                        </td>
                        <td class="center"><?= number_format($page->idx--); ?></td>
                        <td class="center"><div><?= ($val['scmNo'] == DEFAULT_CODE_SCMNO ? '본사' : $val['companyNm']); ?></div></td>
                        <td class="center"><?php
                            if ($isLimit) {
                                ?>
                                <span class="notice-danger js-tooltip" title="운영자 로그인을 5회 이상 실패하여 접속이 제한된 아이디입니다."></span>
                                <?php
                            }
                            ?>
                            <?= $val['managerId']; ?>
                            <?php if ($val['managerNickNm']) {
                                echo '&nbsp;/&nbsp;' . $val['managerNickNm'];
                            } ?>
                            <?= $superText ?>
                        </td>
                        <td class="center"><div><?= $val['managerNm']; ?></div></td>
                        <td class="center"><input type="button" value="권한보기" class="btn btn-white btn-view-unit-permission"></td>
                    </tr>
                    <?php
                    $i++;
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="6">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="text-center"><?php echo $page->getPage('call_manager_search(\'PAGELINK\')');?></div>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 운영자 리스트 체크박스 이벤트
        $('#managerList input[name="manage_sno[]"]').click(act_manage_list_checkbox);

        // 권한보기
        $('#managerList .btn-view-unit-permission').click(function () {
            call_unit_permission(this);
        });
    });
    //-->
</script>