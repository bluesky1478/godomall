<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?>
    </h3>
    <div class="btn-group">
        <input type="button" value="배너 그룹 등록" class="btn btn-red-line js-group-register" />
    </div>
</div>

<form id="frmSearch" name="frmSearch" method="get" action="" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>

    <div class="table-title gd-help-manual">
        배너 그룹 검색
    </div>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td class="form-inline">
                    <?php echo gd_select_box('key', 'key', ['all' => '=통합검색=', 'bannerGroupName' => '배너 그룹명', 'skinName' => '스킨코드'], '', $search['key']); ?>
                    <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control width-md" placeholder="키워드를 입력해 주세요." />
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td class="form-inline">
                    <?php echo gd_select_box('treatDateFl', 'treatDateFl', ['regDt' => '등록일', 'modDt' => '수정일'], '', $search['treatDateFl']); ?>
                    <div class="input-group js-datepicker">
                        <input type="text" name="treatDate[start]" value="<?php echo $search['treatDate']['start'];?>" class="form-control width-xs" placeholder="수기입력 가능" />
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="treatDate[end]" value="<?php echo $search['treatDate']['end'];?>" class="form-control width-xs" placeholder="수기입력 가능" />
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    <?= gd_search_date(gd_isset($req['searchPeriod'], -1), 'treatDate') ?>
                </td>
            </tr>
            <tr>
                <th>구분</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bannerGroupDeviceType" value="" <?php echo gd_isset($checked['bannerGroupDeviceType']['']); ?> />전체</label>
                    <?php foreach ($bannerDeviceType as $dKey => $dVal) {?>
                        <label class="radio-inline"><input type="radio" name="bannerGroupDeviceType" value="<?php echo $dKey;?>" <?php echo gd_isset($checked['bannerGroupDeviceType'][$dKey]); ?> /><?php echo $dVal;?>쇼핑몰</label>
                    <?php }?>
                </td>
            </tr>
            <tr>
                <th>디자인 스킨</th>
                <td class="form-inline">
                    <select name="skinName" class="form-control width-2xl">
                    </select>
                </td>
            </tr>
            <tr>
                <th>배너 그룹 종류</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bannerGroupType" value="" <?php echo gd_isset($checked['bannerGroupType']['']); ?> />전체</label>
                    <?php
                    foreach($bannerGroupTypeFl as $gKey => $gVal){
                        echo '<label class="radio-inline"><input type="radio" name="bannerGroupType" value="' . $gKey . '" ' .gd_isset($checked['bannerGroupType'][$gKey]) .' />' . $gVal . '</label>';
                    }
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black"/>
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?php echo number_format($page->recode['total']); ?></strong>개 /
            전체 <strong><?php echo number_format($page->recode['amount']); ?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
</form>
<form id="frmList" name="frmList" action="" method="post">
    <input type="hidden" name="mode" />
    <table class="table table-rows table-fixed">
        <colgroup>
            <col class="width3p"/>
            <col class="width5p"/>
            <col class="width15p" />
            <col class="width20p" />
            <col class="width20p" />
            <col class="width10p" />
            <col class="width5p" />
            <col class="width10p" />
            <col class="width5p" />
        </colgroup>
        <thead>
        <tr>
            <th><input class="js-checkall" type="checkbox" data-target-name="sno"></th>
            <th>번호</th>
            <th>그룹 종류</th>
            <th>디자인 스킨</th>
            <th>배너 그룹명</th>
            <th>구분</th>
            <th>등록수</th>
            <th>등록일/수정일</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {
                // 적용 스킨
                $skinName = $designSkin[$val['bannerGroupDeviceType']][$val['skinName']];
                if (empty($skinName) === true) {
                    $skinName = $val['skinName'] . ' [스킨정보없음]';
                }
                ?>
                <tr class="text-center">
                    <td><input name="sno[]" type="checkbox" value="<?php echo $val['sno']; ?>" <?php if ($val['bannerCnt'] > 0) { echo 'disabled="disabled"'; } ?>/></td>
                    <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                    <td class="font-kor"><?php echo $bannerGroupTypeFl[$val['bannerGroupType']]; ?></td>
                    <td><?php echo $skinName; ?></td>
                    <td><?php echo $val['bannerGroupName']; ?></td>
                    <td class="font-kor"><?php echo $bannerDeviceType[$val['bannerGroupDeviceType']]; ?>쇼핑몰</td>
                    <td class="font-num"><?php echo $val['bannerCnt']; ?></td>
                    <td class="font-date">
                        <?php echo gd_date_format('Y-m-d', $val['regDt']); ?>
                        <?php
                        if (empty($val['modDt']) === false) {
                            echo '<br/>' . gd_date_format('Y-m-d', $val['modDt']);
                        }
                        ?>
                    </td>
                    <td><button type="button" class="btn btn-white btn-xs js-group-modify" data-sno="<?php echo $val['sno']; ?>">수정</button></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="9">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <input type="button" value="선택 삭제" class="btn btn-white js-group-remove-selected" />
        </div>
    </div>
</form>

<div class="center"><?php echo $page->getPage(); ?></div>

<script type="text/javascript">
    $(document).ready(function () {
        // 정렬
        $('select[name=\'sort\']').change(function () {
            $('#frmSearch').submit();
        });

        // 노출수
        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearch').submit();
        });

        // 구분에 따른 스킨 출력
        bannerGroupSkinChange();
        $('#frmSearch input:radio[name=bannerGroupDeviceType]').click(function () {
            bannerGroupSkinChange();
        });
    });

    // 배너 그룹 등록
    $('.js-group-register').click(function (e) {
        $.get('layer_banner_group.php', '', function (data) {
            BootstrapDialog.show({
                title: '배너 그룹 등록',
                message: $(data),
                closable: true
            });
        });
    });

    // 배너 그룹 수정
    $('.js-group-modify').click(function (e) {
        if ($(this).data('sno') == '') {
            alert('삭제할 배너 그룹 정보가 없습니다.');
            return;
        }
        var params = {
            sno: $(this).data('sno')
        };
        $.get('layer_banner_group.php', params, function (data) {
            BootstrapDialog.show({
                title: '배너 그룹 수정',
                message: $(data),
                closable: true
            });
        });
    });

    // 선택한 배너 그룹 삭제
    $('.js-group-remove-selected').click(function () {
        var chkCnt = $('input[name=\'sno[]\']:checkbox:checked').length;

        if (chkCnt < 1) {
            BootstrapDialog.show({
                title: '선택한 배너 그룹 삭제',
                type: BootstrapDialog.TYPE_WARNING,
                message: '삭제할 배너 그룹을 선택해 주세요.',
            });
            return;
        }

        BootstrapDialog.show({
            title: '선택한 배너 그룹 삭제',
            message: '선택한 ' + chkCnt + ' 개의 배너 그룹을 정말로 삭제 하시겠습니까? 삭제시 복구가 불가능합니다.',
            buttons: [
            {
                id: 'btn-cancel',
                label: '삭제 취소',
                action: function(dialogItself){
                    dialogItself.close();
                }
            },
            {
                id: 'btn-del',
                label: '선택한 배너 그룹 삭제',
                cssClass: 'btn-danger',
                action: function(dialog) {
                    var $delButton = this;
                    var $cancelButton = dialog.getButton('btn-cancel');
                    $delButton.disable();
                    $cancelButton.disable();
                    $delButton.spin();
                    dialog.setClosable(false);
                    dialog.setMessage('선택한 ' + chkCnt + ' 개의 배너 그룹을 삭제 중입니다.');

                    $('#frmList input[name=\'mode\']').val('banner_group_delete_selected');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('target', 'ifrmProcess');
                    $('#frmList').attr('action', './banner_ps.php');
                    $('#frmList').submit();
                }
            }
            ]
        });
        return;
    });

    /**
     * 구분에 따른 스킨 출력
     */
    function bannerGroupSkinChange() {
        var allSkinOption = '<?php echo implode('', $skinList['all']);?>';
        var frontSkinOption = '<?php echo implode('', $skinList['front']);?>';
        var mobileSkinOption = '<?php echo implode('', $skinList['mobile']);?>';
        var selectDeviceType = $('#frmSearch input:radio[name=bannerGroupDeviceType]:checked').val();
        var selectSkinOption = '<option value="">= 디자인 스킨 검색 =</option>';

        // 구분 선택에 따른 select option 값
        if (selectDeviceType == '') {
            selectSkinOption = selectSkinOption + allSkinOption;
        } else if (selectDeviceType == 'front') {
            selectSkinOption = selectSkinOption + frontSkinOption;
        } else {
            selectSkinOption = selectSkinOption + mobileSkinOption;
        }

        // select option 처리
        $('#frmSearch select[name=skinName]').html(selectSkinOption);
    }
</script>
