<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-xs"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tr>
                <th>검색어</th>
                <td class="form-inline">
                    <?= gd_select_box(
                        'key', 'key', [
                        'managerId'     => '아이디',
                        'managerNm'     => '이름',
                        'managerNickNm' => '닉네임',
                    ], '', $search['key'], '=통합검색=', null, 'form-control'
                    ); ?>
                    <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                </td>
                <td>
                    <input type="button" value="검색" class="btn btn-black btn-hf"  onclick="layer_list_search(); ">
                </td>
            </tr>
        </table>
    </div>
</div>
<div>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width10p center">선택</th>
            <th class="width10p center">번호</th>
            <th class="width50p center">아이디/닉네임</th>
            <th class="width20p center">이름</th>
            <th class="width20p center">직원여부</th>
            <th class="width20p center">등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php

        if (gd_isset($data) && is_array($data)) {

            foreach ($data as $key => $val) {

                $superText = '';
                if ($val['isSuper'] == 'y') {
                    $superText = ($val['sno'] == 1) ? '<br/><span class="text-blue">(최고운영자)</span>' : '<br/><span class="text-blue">(대표운영자)</span>';
                }

                ?>
                <tr>
                    <td class="center">
                        <input type="radio" id="layer_manage_sno" name="layer_manage_sno" value="<?= $val['sno']; ?>" />
                        <input type="hidden" id="permissionFl<?= $val['sno']; ?>" value="<?= $val['permissionFl']; ?>" />
                    </td>
                    <td class="center"><?= number_format($page->idx--); ?></td>
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
                    <td class="center"><?= $employeeList[$val['employeeFl']] ?></td>
                    <td class="center"><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
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


    <div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
</div>

<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black" onclick="select_code();" /></div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });

    });

    function layer_list_search(pagelink) {

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }

        var isUseProvider = '<?=gd_use_provider()?>';
        //var defaultCodeScmNo = '<?=DEFAULT_CODE_SCMNO ?>';
        var parentScmNO = parent.$('input:hidden[name="scmNo"]').val();
        var parentScmFl = parent.$('input[name=scmFl]:checked').val();

        if (!isUseProvider) {   //공급사앱을 사용하지 않으면
            scmFl = 'n';
            //if (parentScmNO != defaultCodeScmNo) {
            //scmFl = 'y';
            //}
        } else {
            scmFl = parentScmFl;
        }

        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'mode': '<?php echo $mode?>',
            'key': $('select[name="key"]').val(),
            'keyword': $('input[name="keyword"]').val(),
            'scmNo' : parentScmNO,
            'scmFl' : scmFl,
            'pagelink': pagelink
        };

        $.get('../share/layer_manage.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    function select_code() {

        if ($('input[id*=\'layer_manage_sno\']:checked').length == 0) {
            alert('운영자를 선택해 주세요!');
            return false;
        }

        $('input[id*=\'layer_manage_sno\']:checked').each(function () {
            var manageSno = $(this).val();
            var isSuper = $('.manage-btn').data('issuper');
            parent.change_menu_list_layout(manageSno, 'bring', isSuper);
            var dialog = BootstrapDialog.dialogs[BootstrapDialog.currentId];
            if (dialog) {
                dialog.close();
            }
        });

    }
    //-->
</script>
