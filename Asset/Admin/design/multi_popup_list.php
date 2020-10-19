<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?>
    </h3>
    <div class="btn-group">
        <a href="./multi_popup_register.php" class="btn btn-red-line" role="button">멀티팝업창 등록</a>
    </div>
</div>

<form id="frmSearch" name="frmSearch" method="get" action="" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?=$search['detailSearch']; ?>"/>

    <div class="table-title">
        멀티팝업창 검색
    </div>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3" >
                    <div class="form-inline">
                        <?=gd_select_box('key', 'key', ['all' => '=통합검색=', 'popupTitle' => '멀티팝업 제목'], '', $search['key']); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control width-md" placeholder="키워드를 입력해 주세요." />
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?=gd_select_box('treatDateFl', 'treatDateFl', ['regDt' => '등록일', 'modDt' => '수정일'], '', $search['treatDateFl']); ?>
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[start]" value="<?=$search['treatDate']['start'];?>" class="form-control width-xs" placeholder="수기입력 가능" />
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[end]" value="<?=$search['treatDate']['end'];?>" class="form-control width-xs" placeholder="수기입력 가능" />
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date(gd_isset($req['searchPeriod'], -1), 'treatDate') ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>구분</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="displayFl" value="" <?=gd_isset($checked['displayFl']['']); ?> />전체</label>
                    <?php foreach ($displayFl as $dKey => $dVal) {?>
                        <label class="radio-inline"><input type="radio" name="displayFl" value="<?=$dKey;?>" <?=gd_isset($checked['displayFl'][$dKey]); ?> /><?=$dVal;?>쇼핑몰</label>
                    <?php }?>
                </td>
            </tr>
            </tbody>
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-sm"/>
                <col class="width-xl"/>
            </colgroup>
            <tbody class="js-search-detail" class="display-none">
            <tr>
                <th>출력여부</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="popupUseFl" value="" <?=gd_isset($checked['popupUseFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="popupUseFl" value="y" <?=gd_isset($checked['popupUseFl']['y']); ?> />출력</label>
                    <label class="radio-inline"><input type="radio" name="popupUseFl" value="n" <?=gd_isset($checked['popupUseFl']['n']); ?> />미출력</label>
                </td>
                <th>출력일자</th>
                <td>
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" name="printDt" value="<?=$search['printDt']; ?>" maxlength="10" class="form-control" />
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>멀티팝업창 페이지</th>
                <td>
                    <div class="form-inline">
                        <select name="popupSkin" class="form-control">
                            <option value="">전체보기</option>
                            <?php
                            foreach ($getPopupSkin as $pKey => $pVal) {
                                echo '<option value="' . $pKey . '" ' . gd_isset($selected['popupSkin'][$pKey]) . '>' . $pVal[0] . '</option>' . PHP_EOL;
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <th>창 종류</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="popupKindFl" value="" <?=gd_isset($checked['popupKindFl']['']); ?> />전체</label>
                    <?php
                    foreach ($getPopupKindFl as $pKey => $pVal) {
                        echo '<label class="radio-inline"><input type="radio" name="popupKindFl" value="' . $pKey . '" ' . gd_isset($checked['popupKindFl'][$pKey]) . ' />' . $pVal . '</label>' . PHP_EOL;
                    }
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle">상세검색<span>펼침</span></button>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black"/>
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?=number_format($page->recode['total']); ?></strong>개 /
            전체 <strong><?=number_format($page->recode['amount']); ?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
</form>

<form id="frmList" name="frmList" action="" method="post">
    <input type="hidden" name="mode" />
    <table class="table table-rows table-fixed">
        <colgroup>
            <col class="width5p"/>
            <col class="width5p"/>
            <col class="width7p"/>
            <col class="width10p"/>
            <col class="width15p"/>
            <col class="width15p"/>
            <col class="width5p"/>
            <col class="width10p"/>
            <col class="width10p"/>
            <col class="width10p"/>
            <col class="width5p"/>
            <col class="width5p"/>
        </colgroup>
        <thead>
        <tr>
            <th><input class="js-checkall" type="checkbox" data-target-name="sno"></th>
            <th>번호</th>
            <th>유형</th>
            <th>멀티팝업제목</th>
            <th>출력기간/시간</th>
            <th>멀티팝업 노출 페이지</th>
            <th>출력</th>
            <th>창정보</th>
            <th>창크기</th>
            <th>등록일 / 수정일</th>
            <th>보기</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            $arrPopupUseFl = ['y' => '<span class="text-darkblue">출력</span>', 'n' => '<span class="text-orange">미출력</span>', 'x' => '<span class="text-darkred">종료</span>'];
            foreach ($data as $key => $val) {
                // 특정기간동안 멀티팝업창 열림
                if ($val['popupPeriodOutputFl'] === 'y') {
                    $tmp1 = $val['popupPeriodSDate'] . ' ' . substr($val['popupPeriodSTime'], 0, 5) . ' ~';
                    $tmp2 = $val['popupPeriodEDate'] . ' ' . substr($val['popupPeriodETime'], 0, 5);

                    $popupDateStr = '<span class="font-date">' . $tmp1 . '<br />' . $tmp2 . '</span>';
                    unset($tmp1, $tmp2);

                    // 날짜 체크
                    $checkDate = date('Y-m-d H:i:s');
                    $checkDateS = $val['popupPeriodSDate'] . ' ' . $val['popupPeriodSTime'];
                    $checkDateE = $val['popupPeriodEDate'] . ' ' . $val['popupPeriodETime'];
                    if ($checkDate <= $checkDateS || $checkDate >= $checkDateE) {
                        $val['popupUseFl'] = 'x';
                    }
                    unset($checkDate, $checkDateS, $checkDateE);
                } // 특정기간동안 특정시간에만 멀티팝업창 열림
                elseif ($val['popupPeriodOutputFl'] === 't') {
                    $tmp1 = $val['popupPeriodSDate'] . ' ~ ' . $val['popupPeriodEDate'];
                    $tmp2 = substr($val['popupPeriodSTime'], 0, 5) . ' ~ ' . substr($val['popupPeriodETime'], 0, 5);

                    $popupDateStr = '<span class="font-date">' . $tmp1 . '<br />' . $tmp2 . '</span>';
                    unset($tmp1, $tmp2);

                    // 날짜 체크
                    $checkDate = date('Y-m-d');
                    $checkTime = date('H:i:s');
                    if ($checkDate >= $val['popupPeriodSDate'] && $checkDate <= $val['popupPeriodEDate'] && $checkTime >= $val['popupPeriodSTime'] && $checkTime <= $val['popupPeriodETime']) {
                        $val['popupUseFl'] = $val['popupUseFl'];
                    } else {
                        $val['popupUseFl'] = 'x';
                    }
                    unset($checkDate, $checkTime);
                } // 항상 멀티팝업창 열림
                else {
                    $popupDateStr = '<span class="text-darkblue">항상 멀티팝업창이 열림</span>';
                }

                // 화면보기
                if ($val['popupKindFl'] === 'window') {
                    $preView = sprintf("popup({url:'" . URI_HOME . "popup/popup.php?sno=%s',width:'%s',height:'%s'})", $val['sno'], $val['popupSizeW'], $val['popupSizeH']);
                } else {
                    $preView = sprintf("popup({url:'" . URI_HOME . "popup/popup.php?sno=%s',width:'%s',height:'%s'})", $val['sno'], $val['popupSizeW'], ($val['popupSizeH'] + 10));
                }
                unset($display);
                $displayNm = [];
               if($val['pcDisplayFl']=='y') {
                   $displayNm[] =  "PC쇼핑몰";
               }

                if($val['mobileDisplayFl']=='y') {
                    $displayNm[] =  "모바일쇼핑몰";
                }
                ?>
                <tr class="text-center">
                    <td><input name="sno[]" type="checkbox" value="<?=$val['sno']; ?>" /></td>
                    <td class="font-num"><?=number_format($page->idx--); ?></td>
                    <td>
                        <?=implode("<br>",$displayNm);?>
                    </td>
                    <td>
                        <a href="./multi_popup_register.php?sno=<?=$val['sno']; ?>"><?=$val['popupTitle']; ?></a>
                    </td>
                    <td><?=$popupDateStr; ?></td>
                    <td>
                        <span class="eng text-green"><?=$val['popupPageUrl']; ?>
                            <?php
                            if (trim($val['popupPageParam']) !== '') {
                                echo '<br />(' . $val['popupPageParam'] . ')';
                            }
                            ?>
                        </span>
                    </td>
                    <td><?=$arrPopupUseFl[$val['popupUseFl']]; ?></td>
                    <td><span class="bold"><?=$getPopupKindFl[$val['popupKindFl']]; ?></span><br/><?=gd_isset($getPopupSkin[$val['popupSkin']][0]); ?>
                    </td>
                    <td>
                        <span class="number text-green"><?=$val['popupSlideViewW'] . ' x ' . ($val['popupSlideViewH']+$val['popupSlideThumbH']); ?></span><br/>
                        <span class="number text-darkblue"><?=$val['popupPositionT'] . ' x ' . $val['popupPositionL']; ?></span>
                    </td>
                    <td class="font-date">
                        <?=gd_date_format('Y-m-d', $val['regDt']); ?>
                        <?php
                        if (empty($val['modDt']) === false) {
                            echo '<br/>' . gd_date_format('Y-m-d', $val['modDt']);
                        }
                        ?>
                    </td>
                    <td><input type="button" value="화면보기" onclick="layer_popup_view(<?=$val['sno']?>)" class="btn btn-white btn-sm" /></td>
                    <td><a href="./multi_popup_register.php?sno=<?=$val['sno']; ?>" class="btn btn-white btn-sm" role="button">수정</a></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="12">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-action">
        <input type="button" value="선택 삭제" class="btn btn-white js-remove_selected mgl10" />
    </div>
</form>

<div class="center"><?=$page->getPage(); ?></div>

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
    });


    // 선택한 멀티팝업 삭제
    $('.js-remove_selected').click(function () {
        var chkCnt = $('input[name=\'sno[]\']:checkbox:checked').length;

        if (chkCnt < 1) {
            BootstrapDialog.show({
                title: '선택한 멀티팝업 삭제',
                type: BootstrapDialog.TYPE_WARNING,
                message: '삭제할 멀티팝업을 선택해 주세요.',
            });
            return;
        }

        BootstrapDialog.show({
            title: '선택한 멀티팝업 삭제',
            message: '선택한 ' + chkCnt + ' 개의 멀티팝업을 정말로 삭제 하시겠습니까? 삭제시 복구가 불가능합니다.',
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
                    label: '선택한 멀티팝업 삭제',
                    cssClass: 'btn-danger',
                    action: function(dialog) {
                        var $delButton = this;
                        var $cancelButton = dialog.getButton('btn-cancel');
                        $delButton.disable();
                        $cancelButton.disable();
                        $delButton.spin();
                        dialog.setClosable(false);
                        dialog.setMessage('선택한 ' + chkCnt + ' 개의 멀티팝업을 삭제 중입니다.');

                        $('#frmList input[name=\'mode\']').val('delete_selected');
                        $('#frmList').attr('method', 'post');
                        $('#frmList').attr('target', 'ifrmProcess');
                        $('#frmList').attr('action', './multi_popup_ps.php');
                        $('#frmList').submit();
                    }
                }]
        });
        return;
    });

    /**
     *멀티팝업 미리 보기
     *
     */
    function layer_popup_view(sno)
    {
        var loadChk	= $('#multi_popup_preview').length;

        $.post('../design/multi_popup_preview.php',{'page':'list','sno':sno}, function(data){
            if (loadChk == 0) {
                data = '<div id="multi_popup_preview">'+data+'</div>';
            }
            var layerForm = data;

            BootstrapDialog.show({
                title:"멀티 팝업 미리보기",
                size:BootstrapDialog.SIZE_WIDE_LARGE,
                message: $(layerForm),
                closable: true
            });
        });
    }

</script>
