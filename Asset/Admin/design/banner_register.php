<form id="frmBanner" name="frmBanner" method="post" action="./banner_ps.php" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?php echo $mode; ?>"/>
    <input type="hidden" name="sno" value="<?php echo gd_isset($data['sno']); ?>"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>배너 정보를 <?php echo $modeTxt; ?> 합니다.</small>
        </h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?=$adminList;?>');" />
            <input type="submit" value="배너 <?php echo $modeTxt;?>" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title gd-help-manual">
        배너 정보
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">구분</th>
            <td class="form-inline">
                <?php if ($mode === 'modify') {?>
                    <input type="hidden" name="bannerGroupDeviceType" value="<?php echo $data['bannerGroupDeviceType'];?>" />
                    <span class="bold"><?php echo $bannerDeviceType[$data['bannerGroupDeviceType']];?>쇼핑몰</span>
                <?php } else {?>
                    <?php foreach ($bannerDeviceType as $dKey => $dVal) {?>
                        <label class="radio-inline">
                            <input type="radio" name="bannerGroupDeviceType" value="<?php echo $dKey;?>" <?php echo $checked['bannerGroupDeviceType'][$dKey]; ?> /><?php echo $dVal;?>쇼핑몰
                        </label>
                    <?php }?>
                <?php }?>
            </td>
        </tr>
        <tr>
            <th class="require">디자인 스킨</th>
            <td class="form-inline">
                <?php if ($mode === 'modify') {?>
                    <input type="hidden" name="skinName" value="<?php echo $data['skinName'];?>" />
                    <span class="bold"><?php echo $data['selectedSkinName'];?></span>
                <?php } else {?>
                    <select name="skinName" class="form-control width-2xl">
                    </select>
                <?php }?>
            </td>
        </tr>
        <tr>
            <th class="require">배너 그룹</th>
            <td class="form-inline">
                <select name="bannerGroupCode" class="form-control width-2xl">
                </select>
            </td>
        </tr>
        <tr>
            <th>치환코드</th>
            <td class="form-inline">
                <div id="bannerGroupCodeString" class="display-inline-block"></div>
                <div id="bannerGroupCodeCopy" class="display-none"><button type="button" title="치환코드 복사" class="btn btn-white btn-sm js-clipboard" data-clipboard-text="">복사하기</button></div>
            </td>
        </tr>
        <tr>
            <th>배너 링크 주소</th>
            <td class="form-inline">
                <input type="text" name="bannerLink" value="<?php echo $data['bannerLink']; ?>" class="form-control width70p"/>
                <select name="bannerTarget" class="form-control">
                    <option value="">↓ 타겟을 선택하세요.</option>
                    <option value="" selected="selected">현재창</option>
                    <?php
                    foreach ($bannerTargetKindFl as $tKey => $tVal) {
                        echo '<option value="' . $tKey . '" ' . gd_isset($selected['bannerTarget'][$tKey]) . '>' . $tVal . '</option>' . PHP_EOL;
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th class="require">배너 이미지 설명</th>
            <td class="form-inline">
                <input type="text" name="bannerImageAlt" value="<?php echo $data['bannerImageAlt']; ?>" maxlength="50" class="form-control js-maxlength width-2xl"/>
                <div>
                    <span class="notice-info notice-sm">배너 이미지의 alt 와 title 속성의 내용이며, 최대 50자 입니다.</span>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">배너 이미지</th>
            <td class="form-inline">
                <div class="display-block">
                    <input type="file" name="bannerImageFile" value="" class="form-control width80p"/>
                    <input type="hidden" name="bannerImage" value="<?php echo $data['bannerImage'];?>" />
                </div>
                <?php if (empty($data['bannerImage']) === false) {?>
                    <div class="display-inline-block pdt10">
                        <?php
                        if (isset($data['bannerImageInfo']) === true) {
                            // 이미지 크기를 750px 으로 제한함 너무 크면 화면에서 벗어남
                            $tmpWidth = '';
                            if ($data['bannerImageInfo']['width'] > 750) {
                                $tmpWidth = 750;
                            }
                            echo gd_html_banner_image($data['bannerImagePath'] . $data['bannerImage'], $data['bannerImageAlt'], $tmpWidth);
                        }
                        ?>
                    </div>
                    <div class="display-inline-block pdt10 mgl20 vtop">
                        <?php
                        if (isset($data['bannerImageInfo']) === true) {
                            echo '크기 : ' . $data['bannerImageInfo']['width'] . 'px X ' . $data['bannerImageInfo']['height'] . 'px <br/>';
                            echo '용량 : ' . gd_byte2str($data['bannerImageInfo']['size']) . '<br/>';
                            echo '형식 : ' . $data['bannerImageInfo']['mime'];
                            echo ' <a class="btn-link" href="' . UserFilePath::data('skin', $data['bannerImagePath'] . $data['bannerImage'])->www() . '" target="_blank">[자세히 보기]</a><br/><br/>';
                        }
                        ?>
                        <div><label><input type="checkbox" name="bannerImage_del" value="y"> <span class="text-red">체크시 삭제</span></label></div>
                    </div>
                <?php }?>
            </td>
        </tr>
        <tr>
            <th class="require">노출 여부</th>
            <td class="form-inline">
                <label class="radio-inline"><input type="radio" name="bannerUseFl" value="y" <?php echo gd_isset($checked['bannerUseFl']['y']); ?> />노출</label>
                <label class="radio-inline"><input type="radio" name="bannerUseFl" value="n" <?php echo gd_isset($checked['bannerUseFl']['n']); ?> />미노출</label>
            </td>
        </tr>
        <tr>
            <th>노출 기간</th>
            <td class="form-inline">
                <div>
                    <label class="radio-inline"><input type="radio" name="bannerPeriodOutputFl" value="n" <?php echo gd_isset($checked['bannerPeriodOutputFl']['n']); ?> /> 상시 노출</label>
                    <label class="radio-inline"><input type="radio" name="bannerPeriodOutputFl" value="y" <?php echo gd_isset($checked['bannerPeriodOutputFl']['y']); ?> /> 기간 노출</label>
                </div>

                <div id="bannerPeriodDate" class="form-inline mgt10">
                    <div class="pdl5 mg5">
                        시작일 :
                        <div class="input-group js-datetimepicker">
                            <input type="text" name="bannerPeriodSDateY" value="<?php echo $data['bannerPeriodSDateY']; ?>" class="form-control width-md" placeholder="시작일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                    </div>
                    <div class="pdl5 mg5">
                        종료일 :
                        <div class="input-group js-datetimepicker">
                            <input type="text" name="bannerPeriodEDateY" value="<?php echo $data['bannerPeriodEDateY']; ?>" class="form-control width-md" placeholder="종료일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var frmObj = $('#frmBanner');

        // 배너 정보 저장
        frmObj.validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                bannerGroupCode: "required",
                bannerImageAlt: "required"
            },
            messages: {
                bannerGroupCode: {
                    required: '배너 그룹을 선택해 주세요.'
                },
                bannerImageAlt: {
                    required: '배너 이미지 설명을 입력해 주세요.'
                }
            }
        });

        <?php if ($mode === 'register') {?>
        // 구분에 따른 스킨 출력
        $('#frmBanner input:radio[name=bannerGroupDeviceType]').each(function () {
            setBannerGroupSkinChange();
            setBannerGroupCode();
        });
        $('#frmBanner input:radio[name=bannerGroupDeviceType]').click(function () {
            setBannerGroupSkinChange();
            setBannerGroupCode();
        });

        // 디자인 스킨에 따른 배너 그룹 선택 선택
        $('#frmBanner select[name=skinName]').each(function () {
            setBannerGroupCode();
        });
        $('#frmBanner select[name=skinName]').change(function () {
            setBannerGroupCode();
        });
        <?php }?>
        <?php if ($mode === 'modify') {?>
        $('#frmBanner select[name=bannerGroupCode]').each(function () {
            setBannerGroupCode();
        });
        <?php }?>
        // 배너 그룹 선택에 따른 치환코드 출력
        $('#frmBanner select[name=bannerGroupCode]').change(function () {
            getBannerGroupCodeString();
        });

        // 기간별 노출 설정 여부
        $('input[name=\'bannerPeriodOutputFl\']', frmObj).each(setbannerPeriodOutputFl);
        $('input[name=\'bannerPeriodOutputFl\']', frmObj).click(setbannerPeriodOutputFl);

        /**
         * 디자인 스킨에 따른 배너 그룹 처리
         */
        function setBannerGroupCode() {
            // json data
            var jsonData = '<?php echo $jsonGroupData;?>';

            <?php if ($mode === 'register') {?>
            // 선택된 구분
            var selectedDeviceType = $('#frmBanner input:radio[name=bannerGroupDeviceType]:checked').val();

            // 선택된 디자인 스킨
            var selectedSkinName = $('#frmBanner select[name=skinName]').val();
            <?php }?>
            <?php if ($mode === 'modify') {?>
            // 선택된 구분
            var selectedDeviceType = $('#frmBanner input[name=bannerGroupDeviceType]').val();

            // 선택된 디자인 스킨
            var selectedSkinName = $('#frmBanner input[name=skinName]').val();
            <?php }?>

            // 처리할 그룹코드
            var groupCodeVal = selectedDeviceType + '<?php echo STR_DIVISION;?>' + selectedSkinName;

            // 저장된 배너 그룹 코드
            var bannerGroupCode = '<?php echo $data['bannerGroupCode'];?>';

            // 배너그룹 배열
            var selectOption = new Array();

            // 초기 값
            selectOption[0] = '<option value="">= 디자인 스킨을 선택해 주세요. =</option>';

            // 선택된 디자인 스킨 값이 있는 경우
            if (selectedSkinName != '') {
                // json parse
                var contact = JSON.parse(jsonData);

                // 배너 그룹 비우기
                $('#frmBanner select[name=bannerGroupCode]').html('');

                // 초기값 변경
                selectOption[0] = '<option value="">= 해당 스킨에는 배너 그룹이 없습니다. 배너 그룹을 생성 해주세요. =</option>';

                // json data check
                $.each(contact, function (index, value) {
                    // 해당 적용스킨 값의 json data 가 있는 경우
                    if (index == groupCodeVal) {
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
            $('#frmBanner select[name=bannerGroupCode]').html(selectOption.join('\n'));

            // 치환코드 출력
            getBannerGroupCodeString();
        }

        /**
         * 기간별 노출 설정 여부
         */
        function setbannerPeriodOutputFl() {
            if (this.checked === true && $(this).val() == 'n') {
                $('#bannerPeriodDate').hide();
            } else if (this.checked === true && $(this).val() == 'y') {
                $('#bannerPeriodDate').show();
            }
        }
    });

    <?php if ($mode === 'register') {?>
    /**
     * 구분에 따른 스킨 출력
     */
    function setBannerGroupSkinChange() {
        var frontSkinOption = '<?php echo implode('', $skinList['front']);?>';
        var mobileSkinOption = '<?php echo implode('', $skinList['mobile']);?>';
        var selectDeviceType = $('#frmBanner input:radio[name=bannerGroupDeviceType]:checked').val();
        var selectSkinOption = '<option value="">↓ 디자인 스킨을 선택하세요.</option>';

        // 구분 선택에 따른 select option 값
        if (selectDeviceType == 'front') {
            selectSkinOption = selectSkinOption + frontSkinOption;
        } else {
            selectSkinOption = selectSkinOption + mobileSkinOption;
        }

        // select option 처리
        $('#frmBanner select[name=skinName]').html(selectSkinOption);
    }
    <?php }?>

    /**
     * 배너 그룹 선택시 치환 코드 출력
     */
    function getBannerGroupCodeString() {
        var selectedGroupCode = $('#frmBanner select[name=bannerGroupCode]').val();
        var bannerGroupCodeString = '배너 그룹을 선택하세요';
        var bannerGroupCodeCopy = '';

        if (selectedGroupCode != '') {
            bannerGroupCodeString = '&lt;!--{ @dataBanner(\'' + selectedGroupCode + '\') }--&gt;{.tag}&lt;!--{ / }--&gt;';
            $('#bannerGroupCodeCopy').removeClass('display-none');
            $('#bannerGroupCodeCopy').addClass('display-inline-block');
            $('#bannerGroupCodeCopy button').attr('data-clipboard-text', '<!--{ @dataBanner(\'' + selectedGroupCode + '\') }-->{.tag}<!--{ / }-->');
        } else {
            $('#bannerGroupCodeCopy').removeClass('display-inline-block');
            $('#bannerGroupCodeCopy').addClass('display-none');
        }

        $('#bannerGroupCodeString').html(bannerGroupCodeString);

    }
    //-->
</script>
