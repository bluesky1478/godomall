<form id="frmPopup" name="frmPopup" method="post" action="./popup_ps.php" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?php echo $mode; ?>"/>
    <input type="hidden" name="sno" value="<?php echo $data['sno']; ?>"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?=$adminList;?>');" />
            <input type="submit" value="팝업 <?php echo $modeTxt;?>" class="btn btn-red" />
        </div>
    </div>
    <div class="table-title gd-help-manual">
        팝업창 기본설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <?php if ($gGlobal['isUse'] === true) { ?>
        <tr>
            <th class="require">노출상점</th>
            <td>
                <label class="checkbox-inline"><input type="checkbox" name="mallDisplay[]" value="all" <?php echo $checked['mallDisplay']['all']; ?>>전체</label>
                <?php foreach ($gGlobal['useMallList'] as $mall) { ?>
                    <label class="checkbox-inline"><input type="checkbox" name="mallDisplay[]" value="<?php echo $mall['sno']; ?>" <?php echo $checked['mallDisplay'][$mall['sno']]; ?>><span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span><?php echo $mall['mallName']; ?></label>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th class="require">쇼핑몰 유형</th>
            <td>
                <label class="checkbox-inline"><input type="checkbox" name="pcDisplayFl" data-type="pc" value="y" <?php echo gd_isset($checked['pcDisplayFl']['y']); ?> />PC 쇼핑몰</label>
                <label class="checkbox-inline"><input type="checkbox" name="mobileDisplayFl" data-type="mobile" value="y" <?php echo gd_isset($checked['mobileDisplayFl']['y']); ?> />모바일 쇼핑몰</label>
            </td>
        </tr>
        <tr>
            <th class="require">팝업 제목</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="popupTitle" value="<?php echo $data['popupTitle']; ?>" class="form-control width-2xl"/>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">노출 여부</th>
            <td>
                <label class="radio-inline"><input type="radio" name="popupUseFl" value="y" <?php echo gd_isset($checked['popupUseFl']['y']); ?> />노출</label>
                <label class="radio-inline"><input type="radio" name="popupUseFl" value="n" <?php echo gd_isset($checked['popupUseFl']['n']); ?> />미노출</label>
            </td>
        </tr>
        <tr>
            <th>기간별 노출 설정</th>
            <td>
                <div class="form-inline pdb10">
                    <label class="radio-inline"><input type="radio" name="popupPeriodOutputFl" value="n" <?php echo gd_isset($checked['popupPeriodOutputFl']['n']); ?> /> 항상 팝업창이 열립니다.</label>
                </div>
                <div class="form-inline pdb10">
                    <label class="radio-inline"><input type="radio" name="popupPeriodOutputFl" value="y" <?php echo gd_isset($checked['popupPeriodOutputFl']['y']); ?> /> 특정 기간 동안 팝업창이 열립니다.</label>

                    <div id="popupPeriodDate">
                        <div>
                            시작일 :
                            <div class="input-group js-datepicker">
                                <input type="text" name="popupPeriodSDateY" value="<?php echo $data['popupPeriodSDateY']; ?>" class="form-control width-xs" placeholder="시작일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>

                            <div class="input-group js-timepicker">
                                <input type="text" name="popupPeriodSTimeY" value="<?php echo $data['popupPeriodSTimeY']; ?>" class="form-control width-2xs" placeholder="시간입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div>
                            종료일 :
                            <div class="input-group js-datepicker">
                                <input type="text" name="popupPeriodEDateY" value="<?php echo $data['popupPeriodEDateY']; ?>" class="form-control width-xs" placeholder="종료일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>

                            <div class="input-group js-timepicker">
                                <input type="text" name="popupPeriodETimeY" value="<?php echo $data['popupPeriodETimeY']; ?>" class="form-control width-2xs" placeholder="시간입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="popupPeriodOutputFl" value="t" <?php echo gd_isset($checked['popupPeriodOutputFl']['t']); ?> /> 특정 기간 동안 특정한 시간에만 팝업창이 열립니다.</label>

                    <div id="popupPeriodTime" class="pd5">
                        <div>
                            기간 :
                            <div class="input-group js-datepicker">
                                <input type="text" name="popupPeriodSDateT" value="<?php echo $data['popupPeriodSDateT']; ?>" class="form-control width-xs" placeholder="시작일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                            ~
                            <div class="input-group js-datepicker">
                                <input type="text" name="popupPeriodEDateT" value="<?php echo $data['popupPeriodEDateT']; ?>" class="form-control width-xs" placeholder="종료일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div>
                            시간 :
                            <div class="input-group js-timepicker">
                                <input type="text" name="popupPeriodSTimeT" value="<?php echo $data['popupPeriodSTimeT']; ?>" class="form-control width-xs" placeholder="시작시간입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                            ~
                            <div class="input-group js-timepicker">
                                <input type="text" name="popupPeriodETimeT" value="<?php echo $data['popupPeriodETimeT']; ?>" class="form-control width-xs" placeholder="종료시간입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>오늘하루 보이지 않음</th>
            <td>
                <label class="checkbox-inline"><input type="checkbox" name="todayUnSeeFl" value="y" <?php echo gd_isset($checked['todayUnSeeFl']['y']); ?> />'오늘 하루 보이지 않음' 기능을 사용합니다.</label>

                <div id="todayUnSee" class="form-inline box_form">
                    <div class="pdt10 pdb10">
                        배경 색상 :
                        <input type="text" name="todayUnSeeBgColor" value="<?php echo $data['todayUnSeeBgColor']; ?>" readonly class="form-control width-xs center color-selector"/>
                    </div>
                    <div class="pdb10">
                        글자 색상 :
                        <input type="text" name="todayUnSeeFontColor" value="<?php echo $data['todayUnSeeFontColor']; ?>" readonly class="form-control width-xs center color-selector"/>
                    </div>
                    <div>
                        정렬 :
                        <label class="radio-inline"><input type="radio" name="todayUnSeeAlign" value="left" <?php echo gd_isset($checked['todayUnSeeAlign']['left']); ?> /> 왼쪽</label>
                        <label class="radio-inline"><input type="radio" name="todayUnSeeAlign" value="center" <?php echo gd_isset($checked['todayUnSeeAlign']['center']); ?> /> 가운데</label>
                        <label class="radio-inline"><input type="radio" name="todayUnSeeAlign" value="right" <?php echo gd_isset($checked['todayUnSeeAlign']['right']); ?> /> 오른쪽</label>
                    </div>
                    <p class="notice-info">
                        상단 고정 레이어 팝업창에서는 X(닫기) 버튼에 적용되고, 미사용 시 팝업창 닫기만 적용됩니다.<br />
                        배경색상, 정렬은 ‘상단 고정 레이어’ 에서 적용되지 않습니다.
                    </p>
                </div>
            </td>
        </tr>
        <tr>
            <th>팝업 노출 페이지</th>
            <td>
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-sm"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th colspan="2" class="ta-r">
                            <input type="button" value="노출위치 등록" class="btn btn-gray btn-sm btn-popup-page">
                        </th>
                    </tr>
                    <tr>
                        <th>PC 쇼핑몰</th>
                        <td>
                            <div class="form-inline">
                                <select name="popupPageUrl" class="form-control eng">
                                    <?php
                                    foreach ($getPopupPage as $pKey => $pVal) {
                                        echo '<option value="' . $pVal . '" ' . gd_isset($selected['popupPageUrl'][$pVal]) . '>' . $pKey . ' : ' . $pVal . '</option>';
                                    }
                                    foreach ($getPcPopupPage as $pVal) {
                                        echo '<option value="' . $pVal['pageUrl'] . '" ' . gd_isset($selected['popupPageUrl'][$pVal['pageUrl']]) . ' data-sno="' . $pVal['sno'] . '">' . $pVal['pageName'] . ' : ' . $pVal['pageUrl'] . '</option>';
                                    }
                                    ?>
                                </select>
                                파라메터 : <input type="text" name="popupPageParam" value="<?php echo $data['popupPageParam']; ?>" class="form-control width-lg"/>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>모바일 쇼핑몰</th>
                        <td>
                            <div class="form-inline">
                                <select name="mobilePopupPageUrl" class="form-control eng">
                                    <?php
                                    foreach ($getPopupPage as $pKey => $pVal) {
                                        echo '<option value="' . $pVal . '" ' . gd_isset($selected['mobilePopupPageUrl'][$pVal]) . '>' . $pKey . ' : ' . $pVal . '</option>';
                                    }
                                    foreach ($getMobilePopupPage as $pVal) {
                                        echo '<option value="' . $pVal['pageUrl'] . '" ' . gd_isset($selected['mobilePopupPageUrl'][$pVal['pageUrl']]) . ' data-sno="' . $pVal['sno'] . '">' . $pVal['pageName'] . ' : ' . $pVal['pageUrl'] . '</option>';
                                    }
                                    ?>
                                </select>
                                파라메터 : <input type="text" name="mobilePopupPageParam" value="<?php echo $data['mobilePopupPageParam']; ?>" class="form-control width-lg"/>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="notice-info">
                     팝업 노출 페이지 주소를 선택해 주세요.<br/>
                     파라메터 : 특정 카테고리, 상세 페이지등 팝업을 노출할 경우 해당 페이지의 파라메터를 입력해야 합니다.<br/>
                    예) 카테고리(카테고리NO=001)에 팝업을 노출할 경우 "상품리스트 페이지 : /goods/goods_list.php" 선택 후 cateCd=001 파라메터 입력<br/>
                </div>
            </td>
        </tr>
    </table>

    <div class="use-pc-popup <?php echo $data['pcDisplayFl'] != 'y' ? 'display-none' : ''?>">
        <div class="table-title">
            PC 쇼핑몰
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-2xl"/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>창 종류</th>
                <td colspan="3">
                    <?php
                    foreach ($getPopupKindFl as $pKey => $pVal) {
                        echo '<label class="radio-inline"><input type="radio" name="popupKindFl" value="' . $pKey . '" ' . gd_isset($checked['popupKindFl'][$pKey]) . ' />' . $pVal . '</label>' . PHP_EOL;
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>팝업창 페이지</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="popupSkin" class="form-control">
                            <option value="">--- 팝업창 스킨을 선택해 주세요 ---</option>
                            <?php
                            foreach ($getPopupSkin as $pKey => $pVal) {
                                echo '<option value="' . $pKey . '" ' . gd_isset($selected['popupSkin'][$pKey]) . '>' . $pVal[0] . '</option>' . PHP_EOL;
                            }
                            ?>
                        </select><br />
                        <span class="notice-info">팝업창 스킨은 &quot;사용 스킨&quot;의 &quot;팝업창 / popup&quot; 내에 있는 파일이 노출 됩니다.</span><br/>
                        <span class="notice-info">상단 고정 레이어 팝업창 선택 시 창 종류는 고정 레이어를 선택하시기 바랍니다.</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>창크기</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="popupSizeW" value="<?php echo $data['popupSizeW']; ?>" class="form-control js-number width-3xs center" data-number="4, 2000, 300" />
                        <?php echo gd_select_box('sizeTypeW', 'sizeTypeW', $sizeType, null, $data['sizeTypeW']); ?> X
                        <input type="text" name="popupSizeH" value="<?php echo $data['popupSizeH']; ?>"class="form-control js-number width-3xs center" data-number="4, 2000, 300" />
                        <?php echo gd_select_box('sizeTypeH', 'sizeTypeH', $sizeType, null, $data['sizeTypeH']); ?>
                    </div>
                    <span class="notice-info">
                        <label><input type="checkbox" name="contentImgFl" value="y" <?php echo $checked['contentImgFl']['y']; ?>>에디터에 등록된 이미지를 가로 창크기(width)에 맞춤</label><br />
                        높이(height)는 이미지 크기에 따라 스크롤 또는 여백이 발생될 수 있습니다.
                    </span>
                </td>
                <th>창위치</th>
                <td>
                    <div class="form-inline">
                        <label class="pdb10">상단 : <input type="text" name="popupPositionT" value="<?php echo $data['popupPositionT']; ?>" class="form-control js-number width-2xs center" data-number="4" /> pixel</label><br/>
                        <label>좌측 : <input type="text" name="popupPositionL" value="<?php echo $data['popupPositionL']; ?>" class="form-control js-number width-2xs center" data-number="4" /> pixel</label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>배경색상</th>
                <td colspan="3">
                    <div class="form-inline">
                        <input type="text" name="popupBgColor" value="<?php echo $data['popupBgColor']; ?>" readonly class="form-control width-xs center color-selector"/>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="use-mobile-popup <?php echo $data['mobileDisplayFl'] != 'y' ? 'display-none' : ''?>">
        <div class="table-title">
            모바일 쇼핑몰
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-2xl"/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>창 종류</th>
                <td colspan="3">
                    <?php
                    foreach ($getPopupKindFl as $pKey => $pVal) {
                        if ($pKey != 'layer') continue;
                        echo '<label class="radio-inline"><input type="radio" name="mobilePopupKindFl" value="' . $pKey . '" ' . gd_isset($checked['mobilePopupKindFl'][$pKey]) . ' />' . $pVal . '</label>' . PHP_EOL;
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>팝업창 페이지</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="mobilePopupSkin" class="form-control">
                            <option value="">--- 팝업창 스킨을 선택해 주세요 ---</option>
                            <?php
                            foreach ($getMobilePopupSkin as $pKey => $pVal) {
                                echo '<option value="' . $pKey . '" ' . gd_isset($selected['mobilePopupSkin'][$pKey]) . '>' . $pVal[0] . '</option>' . PHP_EOL;
                            }
                            ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <th>창크기</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="mobilePopupSizeW" value="<?php echo $data['mobilePopupSizeW']; ?>" class="form-control js-number width-3xs center" data-number="4, 2000, 300" />
                        <?php echo gd_select_box('mobileSizeTypeW', 'mobileSizeTypeW', $sizeType, null, $data['mobileSizeTypeW']); ?> X
                        <input type="text" name="mobilePopupSizeH" value="<?php echo $data['mobilePopupSizeH']; ?>"class="form-control js-number width-3xs center" data-number="4, 2000, 300" />
                        <?php echo gd_select_box('mobileSizeTypeH', 'mobileSizeTypeH', $sizeType, null, $data['mobileSizeTypeH']); ?>
                    </div>
                    <span class="notice-info">
                        <label><input type="checkbox" name="mobileContentImgFl" value="y" <?php echo $checked['mobileContentImgFl']['y']; ?>>에디터에 등록된 이미지를 가로 창크기(width)에 맞춤</label><br />
                        높이(height)는 이미지 크기에 따라 스크롤 또는 여백이 발생될 수 있습니다.
                    </span>
                </td>
                <th>창위치</th>
                <td>
                    <div class="form-inline">
                        <label class="pdb10">상단 : <input type="text" name="mobilePopupPositionT" value="<?php echo $data['mobilePopupPositionT']; ?>" class="form-control js-number width-2xs center" data-number="4" /> pixel</label><br/>
                        <label>좌측 : <input type="text" name="mobilePopupPositionL" value="<?php echo $data['mobilePopupPositionL']; ?>" class="form-control js-number width-2xs center" data-number="4" /> pixel</label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>배경색상</th>
                <td colspan="3">
                    <div class="form-inline">
                        <input type="text" name="mobilePopupBgColor" value="<?php echo $data['mobilePopupBgColor']; ?>" readonly class="form-control width-xs center color-selector"/>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        팝업창 내용
    </div>
    <div>
        <!-- editor tool : start -->
        <textarea name="popupContent" id="editor" class="form-control height400"><?php echo $data['popupContent']; ?></textarea>
        <!-- editor tool : end -->
    </div>
</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

        var frmObj = $('#frmPopup');

        // 팝업 정보 저장
        frmObj.validate({
            submitHandler: function (form) {
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);    // 에디터의 내용이 textarea에 적용됩니다.

                <?php if ($gGlobal['isUse'] === true) { ?>
                if(!$('input[name*="mallDisplay"]:checkbox:checked').length){
                    alert('노출상점을 선택해 주세요.')
                    return false;
                }
                <?php } ?>

                if(!$('input[name$="DisplayFl"]:checkbox:checked').length){
                    alert('쇼핑몰 유형을 선택해 주세요.');
                    return false;
                }
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                popupTitle: "required",
                popupSkin: "required",
                popupSizeW: "required",
                popupSizeH: "required"
            },
            messages: {
                popupTitle: {
                    required: '팝업 제목을 입력해 주세요.'
                },
                popupSkin: {
                    required: '팝업창 스킨을 선택해 주세요.'
                },
                popupSizeW: {
                    required: '창 가로크기를 입력해 주세요.'
                },
                popupSizeH: {
                    required: '창 세로크기를 입력해 주세요.'
                },
            }
        });

        // 기간별 노출 설정 여부
        $('input[name=\'popupPeriodOutputFl\']', frmObj).each(setPopupPeriodOutputFl);
        $('input[name=\'popupPeriodOutputFl\']', frmObj).click(setPopupPeriodOutputFl);

        // 오늘 하루 보이지 않음 사용여부
        $('input[name=\'todayUnSeeFl\']', frmObj).each(setTodayUnSeeFl);
        $('input[name=\'todayUnSeeFl\']', frmObj).click(setTodayUnSeeFl);

        $('.btn-popup-page').click(function(){
            var addParam = {
                "layerFormID": 'popupPageForm'
            };
            $.ajax({
                url: './popup_page_list.php',
                type: 'get',
                data: addParam,
                async: false,
                success: function (data) {
                    data = '<div id="' + addParam['layerFormID'] + '">' + data + '</div>';
                    var layerForm = data;
                    BootstrapDialog.show({
                        title: "노출위치 관리",
                        size: get_layer_size('wide'),
                        message: $(layerForm),
                        closable: true,
                    });
                }
            });
        });

        $('input[name$="DisplayFl"]').click(function(){
            var type = $(this).data('type');
            var checked = $(this).prop('checked');

            if (checked === true) {
                $('.use-' + type + '-popup').removeClass('display-none');
            } else {
                $('.use-' + type + '-popup').addClass('display-none');
            }
        });

        $('input[name="popupKindFl"]').click(function(){
            switch ($(this).val()) {
                case 'window':
                    $('select[name="popupSkin"]').val('standard');
                    break;
                default:
                    $('select[name="popupSkin"]').val($(this).val());
                    break;
            }
        });

        <?php if ($gGlobal['isUse'] === true) { ?>
        if($("input[name*='mallDisplay[]']:checked").length == <?php echo count($gGlobal['useMallList']); ?>) {
            $("input[name*='mallDisplay[]']").prop("checked",false);
            $("input[name='mallDisplay[]'][value='all']").prop("checked",true);
        }

        $("input[name*='mallDisplay[]']").click(function () {
            if($(this).is(":checked") && $(this).val() !='all') {
                $("input[name='mallDisplay[]'][value='all']").prop("checked",false);
            }
        });
        <?php } ?>
    });

    /**
     * 기간별 노출 설정 여부
     */
    function setPopupPeriodOutputFl() {
        if (this.checked === true && $(this).val() == 'n') {
            $('#popupPeriodDate').hide();
            $('#popupPeriodTime').hide();
        } else if (this.checked === true && $(this).val() == 'y') {
            $('#popupPeriodDate').show();
            $('#popupPeriodTime').hide();
        } else if (this.checked === true && $(this).val() == 't') {
            $('#popupPeriodDate').hide();
            $('#popupPeriodTime').show();
        }
    }

    /**
     * 오늘 하루 보이지 않음 사용여부
     */
    function setTodayUnSeeFl() {
        if (this.checked === false) {
            $('#todayUnSee').hide();
        } else {
            $('#todayUnSee').show();
        }
    }
    //-->
</script>
