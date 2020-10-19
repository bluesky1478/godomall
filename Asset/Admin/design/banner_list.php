<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?>
    </h3>
    <div class="btn-group">
        <a href="./banner_register.php" class="btn btn-red-line" role="button">배너 등록</a>
    </div>
</div>

<form id="frmSearch" name="frmSearch" method="get" action="" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>

    <div class="table-title gd-help-manual">
        배너 검색
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
                    <?php echo gd_select_box('key', 'key', ['all' => '=통합검색=', 'db.skinName' => '디자인 스킨 코드', 'dbg.bannerGroupName' => '배너 그룹명', 'db.bannerLink' => '배너 링크주소'], '', $search['key']); ?>
                    <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control width-md" placeholder="키워드를 입력해 주세요." />
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td class="form-inline">
                    <?php echo gd_select_box('treatDateFl', 'treatDateFl', ['db.regDt' => '등록일', 'db.modDt' => '수정일'], '', $search['treatDateFl']); ?>
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
                <th>노출 여부</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bannerUseFl" value="" <?php echo gd_isset($checked['bannerUseFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="bannerUseFl" value="y" <?php echo gd_isset($checked['bannerUseFl']['y']); ?> />출력</label>
                    <label class="radio-inline"><input type="radio" name="bannerUseFl" value="n" <?php echo gd_isset($checked['bannerUseFl']['n']); ?> />미출력</label>
                </td>
            </tr>
            <tr>
                <th>배너 그룹</th>
                <td class="form-inline">
                    <select name="bannerGroupCode" class="form-control width-2xl">
                    </select>
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
            <col class="width30p" />
            <col class="width5p" />
            <col class="width10p" />
            <col class="width10p" />
            <col class="width10p" />
            <col class="width15p" />
            <col class="width10p" />
            <col class="width5p" />
        </colgroup>
        <thead>
        <tr>
            <th><input class="js-checkall" type="checkbox" data-target-name="sno"></th>
            <th>번호</th>
            <th>이미지</th>
            <th>구분</th>
            <th>적용스킨</th>
            <th>치환코드</th>
            <th>배너 그룹</th>
            <th>노출</th>
            <th>등록일/수정일</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            $arrBannerUseFl = [
                'y' => '<span class="text-default">노출</span>',
                's' => '<span class="text-close">대기</span>',
                'n' => '<span class="text-close">미노출</span>',
                'x' => '<span class="text-close">종료</span>'
            ];
            foreach ($data as $key => $val) {
                // 디자인 스킨
                $skinName = $designSkin[$val['bannerGroupDeviceType']][$val['skinName']];
                $skinName = str_replace(' ( ', '<br />( ', $skinName);
                if (empty($skinName) === true) {
                    $skinName = $val['skinName'] . '<br />[스킨정보없음]';
                }

                // 노출 기간
                if ($val['bannerPeriodOutputFl'] === 'y') {
                    $tmp1 = $val['bannerPeriodSDate'] . ' ' . substr($val['bannerPeriodSTime'], 0, 5) . ' ~';
                    $tmp2 = $val['bannerPeriodEDate'] . ' ' . substr($val['bannerPeriodETime'], 0, 5);

                    $bannerDateStr = '<span class="font-date text-default">' . $tmp1 . '<br />' . $tmp2 . '</span>';
                    unset($tmp1, $tmp2);

                    // 날짜 체크
                    $checkDate = date('Y-m-d H:i:s');
                    $checkDateS = $val['bannerPeriodSDate'] . ' ' . $val['bannerPeriodSTime'];
                    $checkDateE = $val['bannerPeriodEDate'] . ' ' . $val['bannerPeriodETime'];
                    if ($checkDate <= $checkDateS) {
                        $val['bannerUseFl'] = 's';
                    } elseif ($checkDate >= $checkDateE) {
                        $val['bannerUseFl'] = 'x';
                    }
                    unset($checkDate, $checkDateS, $checkDateE);
                } else {
                    $bannerDateStr = '<span class="text-default">상시 노출</span>';
                }

                // 배너 타켓
                if (empty($val['bannerTarget']) === true) {
                    $bannerTarget = '현재창';
                } else {
                    $bannerTarget = $bannerTargetKindFl[$val['bannerTarget']];
                }
                ?>
                <tr class="text-center mouse-over">
                    <td><input name="sno[]" type="checkbox" value="<?php echo $val['sno']; ?>" /></td>
                    <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                    <td>
                        <div class="banner_image_info">
                            <?php
                            if (empty($val['bannerImage']) === false) {
                                $tmpImgTitle = '이미지 설명 : ' . $val['bannerImageAlt'] . '<br/>링크 : ' . $val['bannerLink'] . '<br/>타켓 : ' . $bannerTarget;
                                $tmpDataTitle = 'data-original-title="' . $tmpImgTitle . '" data-html="true" data-placement="right"';
                                echo gd_html_banner_image($val['bannerImagePath'] . $val['bannerImage'], $val['bannerImageAlt'], 200, 80, 'js-tooltip', $tmpDataTitle);
                            }
                            ?>
                            <div>
                                <?php
                                if (isset($val['bannerImageInfo']) === true) {
                                    echo '크기 : ' . $val['bannerImageInfo']['width'] . 'px X ' . $val['bannerImageInfo']['height'] . 'px <br/>';
                                    echo '용량 : ' . gd_byte2str($val['bannerImageInfo']['size']) . '<br/>';
                                    echo '형식 : ' . $val['bannerImageInfo']['mime'] . '<br/>';
                                }
                                ?>
                            </div>
                        </div>
                    </td>
                    <td class="font-kor"><?php echo $bannerDeviceType[$val['bannerGroupDeviceType']]; ?><br />쇼핑몰</td>
                    <td><?php echo $skinName; ?></td>
                    <td class="font-eng">
                        <button type="button" title="" class="btn btn-gray btn-sm js-popover" data-original-title="치환코드" data-content="<!--{ @dataBanner('<?php echo $val['bannerGroupCode']; ?>') }-->{.tag}<!--{ / }-->"  data-placement="bottom">코드보기</button>
                        <button type="button" title="치환코드 복사" class="btn btn-white btn-sm js-clipboard"  data-clipboard-text="<!--{ @dataBanner('<?php echo $val['bannerGroupCode']; ?>') }-->{.tag}<!--{ / }-->">복사</button>
                    </td>
                    <td>
                        <div class="text-close"><?php echo $bannerGroupTypeFl[$val['bannerGroupType']]; ?></div>
                        <div><?php echo $val['bannerGroupName']; ?></div>
                    </td>
                    <td>
                        <div class="font-kor"><?php echo $arrBannerUseFl[$val['bannerUseFl']]; ?></div>
                        <div class="font-date">(<?php echo $bannerDateStr; ?>)</div>
                    </td>
                    <td class="font-date">
                        <?php echo gd_date_format('Y-m-d', $val['regDt']); ?>
                        <?php
                        if (empty($val['modDt']) === false) {
                            echo '<br/>' . gd_date_format('Y-m-d', $val['modDt']);
                        }
                        ?>
                    </td>
                    <td><a href="./banner_register.php?sno=<?php echo $val['sno']; ?>" class="btn btn-white btn-sm" role="button">수정</a></td>
                    <!--<td><button type="button" class="btn btn-white btn-sm js-remove" data-sno="<?php echo $val['sno']; ?>" data-code="<?php echo $val['bannerGroupCode']; ?>" data-name="<?php echo $val['bannerGroupName']; ?>">삭제</button></td>-->
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
        <div class="pull-left">
            <input type="button" value="선택 삭제" class="btn btn-white js-remove-selected" />
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
        $('#frmSearch input:radio[name=bannerGroupDeviceType]').each(function () {
            bannerGroupSkinChange();
            bannerGroupCode();
        });
        $('#frmSearch input:radio[name=bannerGroupDeviceType]').click(function () {
            bannerGroupSkinChange();
            bannerGroupCode();
        });

        // 디자인 스킨에 따른 배너 그룹 선택 선택
        $('#frmSearch select[name=skinName]').each(function () {
            bannerGroupCode();
        });
        $('#frmSearch select[name=skinName]').change(function () {
            bannerGroupCode();
        });
    });

    // 배너 삭제
    $('.js-remove').click(function () {
        var sno = $(this).data('sno');
        var bannerGroupCode = $(this).data('code');
        var bannerGroupName = $(this).data('name');
        var bannerName = bannerGroupName + ' (코드 : ' + bannerGroupCode + ') 배너';

        BootstrapDialog.show({
            title: '배너 삭제',
            type: BootstrapDialog.TYPE_DANGER,
            message: bannerName + '를 정말로 삭제 하시겠습니까? 삭제시 복구가 불가능합니다.',
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
                label: bannerGroupName + ' 배너 삭제',
                cssClass: 'btn-danger',
                action: function(dialog) {
                    var $delButton = this;
                    var $cancelButton = dialog.getButton('btn-cancel');
                    $delButton.disable();
                    $cancelButton.disable();
                    $delButton.spin();
                    dialog.setClosable(false);
                    dialog.setMessage(bannerName + ' 삭제 중입니다.');

                    $.ajax({
                        type: 'POST'
                        , url: './banner_ps.php'
                        , data: {'mode': 'delete', 'sno': sno, 'bannerGroupCode': bannerGroupCode}
                        , success: function (res) {
                            if (res == '') {
                                dialog.getModalBody().html(bannerName + '가 삭제 되었습니다. 잠시후 완료 됩니다.');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                dialog.getModalBody().html(bannerName + ' 삭제에 실패 하였습니다. <br />실패 이유 : ' + res);
                                setTimeout(function() {
                                    dialog.close();
                                }, 3000);
                            }
                        }
                    });
                }
            }]
        });
        return;
    });

    // 선택한 팝업 삭제
    $('.js-remove-selected').click(function () {
        var chkCnt = $('input[name=\'sno[]\']:checkbox:checked').length;

        if (chkCnt < 1) {
            BootstrapDialog.show({
                title: '선택한 배너 삭제',
                type: BootstrapDialog.TYPE_WARNING,
                message: '삭제할 배너를 선택해 주세요.',
            });
            return;
        }

        BootstrapDialog.show({
            title: '선택한 배너 삭제',
            message: '선택한 ' + chkCnt + ' 개의 배너를 정말로 삭제 하시겠습니까? 삭제시 복구가 불가능합니다.',
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
                label: '선택한 배너 삭제',
                cssClass: 'btn-danger',
                action: function(dialog) {
                    var $delButton = this;
                    var $cancelButton = dialog.getButton('btn-cancel');
                    $delButton.disable();
                    $cancelButton.disable();
                    $delButton.spin();
                    dialog.setClosable(false);
                    dialog.setMessage('선택한 ' + chkCnt + ' 개의 배너를 삭제 중입니다.');

                    $('#frmList input[name=\'mode\']').val('delete_selected');
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

    /**
     * 디자인 스킨에 따른 배너 그룹 처리
     */
    function bannerGroupCode() {
        // json data
        var jsonData = '<?php echo $jsonGroupData;?>';

        // 선택된 디자인 스킨 코드 (구분^|^스킨)
        var selectedSkinNameCode = $('#frmSearch select[name=skinName]').val();

        // 선택된 배너 그룹 코드
        var bannerGroupCode = '<?php echo $search['bannerGroupCode'];?>';

        // 배너그룹 배열
        var selectOption = new Array();

        // 초기 값
        selectOption[0] = '<option value="">= 디자인 스킨을 선택해 주세요. =</option>';

        // 선택된 디자인 스킨 값이 있는 경우
        if (selectedSkinNameCode != '') {
            // json parse
            var contact = JSON.parse(jsonData);

            // 배너 그룹 비우기
            $('#frmSearch select[name=bannerGroupCode]').html('');

            // 초기값 변경
            selectOption[0] = '<option value="">= 해당 스킨에는 배너 그룹이 없습니다. 배너 그룹을 생성 해주세요. =</option>';

            // json data check
            $.each(contact, function (index, value) {
                // 해당 적용스킨 값의 json data 가 있는 경우
                if (index == selectedSkinNameCode) {
                    // 초기값 변경
                    selectOption[0] = '<option value="">↓ 배너 그룹을 선택하세요.</option>';

                    // 배너 그룹 생성
                    $.each(value, function (idx, val) {
                        var selected = '';
                        if (bannerGroupCode == val.bannerGroupCode) {
                            selected = 'selected="selected"';
                        }
                        selectOption[idx + 1] = '<option value="' + val.bannerGroupCode + '" ' + selected + '>' + val.bannerGroupName + '</option>';
                    });
                }
            });
        }

        // 배너 그룹 처리
        $('#frmSearch select[name=bannerGroupCode]').html(selectOption.join('\n'));
    }
</script>
