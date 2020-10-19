<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?>
    </h3>
    <div class="btn-group">
        <a href="./slider_banner_register.php" class="btn btn-red-line" role="button">움직이는 배너 등록</a>
    </div>
</div>

<form id="frmSearch" name="frmSearch" method="get" action="" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>

    <div class="table-title gd-help-manual">
        움직이는 배너 검색
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
                    <?php echo gd_select_box('key', 'key', ['all' => '=통합검색=', 'bannerTitle' => '배너 타이틀', 'bannerCode' => '배너 코드'], '', $search['key']); ?>
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
                    <label class="radio-inline"><input type="radio" name="bannerDeviceType" value="" <?php echo gd_isset($checked['bannerDeviceType']['']); ?> />전체</label>
                    <?php foreach ($bannerDeviceType as $dKey => $dVal) {?>
                        <label class="radio-inline"><input type="radio" name="bannerDeviceType" value="<?php echo $dKey;?>" <?php echo gd_isset($checked['bannerDeviceType'][$dKey]); ?> /><?php echo $dVal;?>쇼핑몰</label>
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
                    <label class="radio-inline"><input type="radio" name="bannerUseFl" value="y" <?php echo gd_isset($checked['bannerUseFl']['y']); ?> />노출</label>
                    <label class="radio-inline"><input type="radio" name="bannerUseFl" value="n" <?php echo gd_isset($checked['bannerUseFl']['n']); ?> />미노출</label>
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
            <col class="width15p" />
            <col class="width5p" />
            <col class="width10p" />
            <col class="width10p" />
            <col class="width15p" />
            <col class="width10p" />
            <col class="width5p" />
            <col class="width5p" />
        </colgroup>
        <thead>
        <tr>
            <th><input class="js-checkall" type="checkbox" data-target-name="sno"></th>
            <th>번호</th>
            <th>이미지</th>
            <th>배너 타이틀</th>
            <th>구분</th>
            <th>적용스킨</th>
            <th>치환코드</th>
            <th>노출</th>
            <th>등록일/수정일</th>
            <th>보기</th>
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
                $skinName = $designSkin[$val['bannerDeviceType']][$val['skinName']];
                $skinName = str_replace(' ( ', '<br />( ', $skinName);
                if (empty($skinName) === true) {
                    $skinName = $val['skinName'] . '<br />[스킨정보없음]';
                }

                // 배너 이미지
                $bannerImage = '';
                $bannerIageAlt = '';
                $tmp = gd_obj2arr(json_decode($val['bannerInfo']));
                foreach ($tmp as $iKey => $iVal) {
                    if ($iKey == '0') {
                        $bannerImage = $tmp['bannerFolder'] . '/' . $iVal['bannerImage'];
                        $bannerImageAlt = $iVal['bannerImageAlt'];
                    } else {
                        continue;
                    }
                }
                unset($tmp);

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
                ?>
                <tr class="text-center">
                    <td><input name="sno[]" type="checkbox" value="<?php echo $val['sno']; ?>" /></td>
                    <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                    <td><?php if (empty($bannerImage) === false) { echo gd_html_banner_image($val['bannerImagePath'] . $bannerImage, $bannerImageAlt, 200, 80); } ?></td>
                    <td><a href="./slider_banner_register.php?sno=<?php echo $val['sno']; ?>"><?php echo $val['bannerTitle']; ?></a></td>
                    <td class="font-kor"><?php echo $bannerDeviceType[$val['bannerDeviceType']]; ?><br />쇼핑몰</td>
                    <td><?php echo $skinName; ?></a></td>
                    <td class="font-eng">
                        <button type="button" title="치환코드" class="btn btn-gray btn-sm js-popover" data-original-title="치환코드" data-content="{=includeWidget('proc/_slider_banner.html', 'bannerCode', '<?php echo $val['bannerCode']; ?>')}" data-placement="bottom">코드보기</button>
                        <button type="button" title="치환코드 복사" class="btn btn-white btn-sm js-clipboard"  data-clipboard-text="{=includeWidget('proc/_slider_banner.html', 'bannerCode', '<?php echo $val['bannerCode']; ?>')}">복사</button>
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
                    <td><input type="button" value="화면보기" onclick="slider_banner_view(<?=$val['sno']?>)" class="btn btn-white btn-sm" /></td>
                    <td><a href="./slider_banner_register.php?sno=<?php echo $val['sno']; ?>" class="btn btn-white btn-sm" role="button">수정</a></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="11">검색된 정보가 없습니다.</td>
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
        bannerSkinChange();
        $('#frmSearch input:radio[name=bannerDeviceType]').click(function () {
            bannerSkinChange();
        });
    });

    // 선택한 팝업 삭제
    $('.js-remove-selected').click(function () {
        var chkCnt = $('input[name=\'sno[]\']:checkbox:checked').length;

        if (chkCnt < 1) {
            BootstrapDialog.show({
                title: '선택한 움직이는 배너 삭제',
                type: BootstrapDialog.TYPE_WARNING,
                message: '삭제할 움직이는 배너를 선택해 주세요.',
            });
            return;
        }

        BootstrapDialog.show({
            title: '선택한 움직이는 배너 삭제',
            message: '선택한 ' + chkCnt + ' 개의 움직이는 배너를 정말로 삭제 하시겠습니까? 삭제시 복구가 불가능합니다.',
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
                label: '선택한 움직이는 배너 삭제',
                cssClass: 'btn-danger',
                action: function(dialog) {
                    var $delButton = this;
                    var $cancelButton = dialog.getButton('btn-cancel');
                    $delButton.disable();
                    $cancelButton.disable();
                    $delButton.spin();
                    dialog.setClosable(false);
                    dialog.setMessage('선택한 ' + chkCnt + ' 개의 움직이는 배너를 삭제 중입니다.');

                    $('#frmList input[name=\'mode\']').val('deleteSliderBanner');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('target', 'ifrmProcess');
                    $('#frmList').attr('action', './slider_banner_ps.php');
                    $('#frmList').submit();
                }
            }
            ]
        });
        return;
    });

    /**
     * 구분에 따른 스킨 / 그룹 출력
     */
    function bannerSkinChange() {
        var allSkinOption = '<?php echo implode('', $skinList['all']);?>';
        var frontSkinOption = '<?php echo implode('', $skinList['front']);?>';
        var mobileSkinOption = '<?php echo implode('', $skinList['mobile']);?>';
        var selectDeviceType = $('#frmSearch input:radio[name=bannerDeviceType]:checked').val();
        var selectSkinOption = '<option value="">= 디자인 스킨 검색 =</option>';
        var selectVal = '';

        // 구분 선택에 따른 select option 값
        if (selectDeviceType == '') {
            selectSkinOption = selectSkinOption + allSkinOption;
        } else if (selectDeviceType == 'front') {
            selectSkinOption = selectSkinOption + frontSkinOption;
            selectVal = "<?php echo $selectVal['front']; ?>";
        } else {
            selectSkinOption = selectSkinOption + mobileSkinOption;
            selectVal = "<?php echo $selectVal['mobile']; ?>";
        }

        // select option 처리
        $('#frmSearch select[name=skinName]').html(selectSkinOption).val(selectVal);

    }

    /**
     *움직이는 배너 미리 보기
     *
     */
    function slider_banner_view( sno)
    {
        var title = "움직이는 배너 미리보기";
        $.get('./slider_banner_preview.php',{ mode : 'preview', sno : sno }, function(data){

            data = '<div id="viewInfoForm">'+data+'</div>';

            var layerForm = data;

            BootstrapDialog.show({
                title:title,
                size: get_layer_size('wide-lg'),
                message: $(layerForm),
                closable: true
            });
        });
    }
</script>
