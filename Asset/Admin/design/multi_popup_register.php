<style>
    .js-multi-popup-preview tbody td {
        border:1px solid #E6E6E6;
    }

</style>
<form id="frmMultiPopup" name="frmMultiPopup" method="post" action="./multi_popup_ps.php" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?=$mode; ?>"/>
    <input type="hidden" name="sno" value="<?=$data['sno']; ?>"/>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?>
        </h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?=$adminList;?>');" />
            <input type="submit" value="멀티팝업 <?=$modeTxt;?>" class="btn btn-red" />
        </div>
    </div>
    <div class="table-title">
        멀티팝업창 기본설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="width-xl"/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">쇼핑몰 유형</th>
            <td colspan="3">
                <label class="checkbox-inline"><input type="checkbox" name="pcDisplayFl" value="y" <?=gd_isset($checked['pcDisplayFl']['y']); ?> />PC 쇼핑몰</label>
                <label class="checkbox-inline"><input type="checkbox" name="mobileDisplayFl" value="y" <?=gd_isset($checked['mobileDisplayFl']['y']); ?> />모바일 쇼핑몰</label>
            </td>
        </tr>
        <tr>
            <th class="require">멀티팝업 제목</th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="popupTitle" value="<?=$data['popupTitle']; ?>" class="form-control width-2xl"/>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">노출 여부</th>
            <td colspan="3">
                <label class="radio-inline"><input type="radio" name="popupUseFl" value="y" <?=gd_isset($checked['popupUseFl']['y']); ?> />노출</label>
                <label class="radio-inline"><input type="radio" name="popupUseFl" value="n" <?=gd_isset($checked['popupUseFl']['n']); ?> />미노출</label>
            </td>
        </tr>
        <tr>
            <th class="require">창 종류</th>
            <td colspan="3">
                <?php
                foreach ($getPopupKindFl as $pKey => $pVal) {
                    echo '<label class="radio-inline"><input type="radio" name="popupKindFl" value="' . $pKey . '" ' . gd_isset($checked['popupKindFl'][$pKey]) . ' />' . $pVal . '</label>' . PHP_EOL;
                }
                ?>
            </td>
        </tr>
        <tr >
            <th class="require">멀티팝업창 페이지</th>
            <td colspan="3">
                <div class="form-inline">
                    <select name="popupSkin" class="form-control">
                        <option value="">--- 멀티팝업창 스킨을 선택해 주세요 ---</option>
                        <?php
                        foreach ($getPopupSkin as $pKey => $pVal) {
                            echo '<option value="' . $pKey . '" ' . gd_isset($selected['popupSkin'][$pKey]) . '>' . $pVal[0] . '</option>' . PHP_EOL;
                        }
                        ?>
                    </select>
                    <span class="notice-info">멀티팝업창 스킨은 &quot;사용 스킨&quot;의 &quot;멀티팝업창 / popup/multi &quot; 내에 있는 파일이 노출 됩니다.</span><br/>
                </div>
            </td>
        </tr>
        <tr>
            <th>창위치</th>
            <td colspan="3">
                <div class="form-inline">
                    <label>상단에서 : <input type="text" name="popupPositionT" value="<?=$data['popupPositionT']; ?>" class="form-control js-number width-2xs center" data-number="4" /> pixel</label>
                    <label>좌측에서 : <input type="text" name="popupPositionL" value="<?=$data['popupPositionL']; ?>" class="form-control js-number width-2xs center" data-number="4" /> pixel</label>
                </div>
            </td>
        </tr>
        <tr>
            <th>기간별 노출 설정</th>
            <td colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="popupPeriodOutputFl" value="n" <?=gd_isset($checked['popupPeriodOutputFl']['n']); ?> /> 항상 멀티팝업창이 열립니다.</label>
                </div>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="popupPeriodOutputFl" value="y" <?=gd_isset($checked['popupPeriodOutputFl']['y']); ?> /> 특정 기간 동안 멀티팝업창이 열립니다.</label>

                    <div id="popupPeriodDate" class="box_form">
                        <div>
                            시작일 :
                            <div class="input-group js-datepicker">
                                <input type="text" name="popupPeriodSDateY" value="<?=$data['popupPeriodSDateY']; ?>" class="form-control width-xs" placeholder="시작일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>

                            <div class="input-group js-timepicker">
                                <input type="text" name="popupPeriodSTimeY" value="<?=$data['popupPeriodSTimeY']; ?>" class="form-control width-2xs" placeholder="시간입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div>
                            종료일 :
                            <div class="input-group js-datepicker">
                                <input type="text" name="popupPeriodEDateY" value="<?=$data['popupPeriodEDateY']; ?>" class="form-control width-xs" placeholder="종료일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>

                            <div class="input-group js-timepicker">
                                <input type="text" name="popupPeriodETimeY" value="<?=$data['popupPeriodETimeY']; ?>" class="form-control width-2xs" placeholder="시간입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="popupPeriodOutputFl" value="t" <?=gd_isset($checked['popupPeriodOutputFl']['t']); ?> /> 특정 기간 동안 특정한 시간에만 멀티팝업창이 열립니다.</label>

                    <div id="popupPeriodTime" class="box_form">
                        <div>
                            기간 :
                            <div class="input-group js-datepicker">
                                <input type="text" name="popupPeriodSDateT" value="<?=$data['popupPeriodSDateT']; ?>" class="form-control width-xs" placeholder="시작일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                            ~
                            <div class="input-group js-datepicker">
                                <input type="text" name="popupPeriodEDateT" value="<?=$data['popupPeriodEDateT']; ?>" class="form-control width-xs" placeholder="종료일자입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div>
                            시간 :
                            <div class="input-group js-timepicker">
                                <input type="text" name="popupPeriodSTimeT" value="<?=$data['popupPeriodSTimeT']; ?>" class="form-control width-xs" placeholder="시작시간입력">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                            ~
                            <div class="input-group js-timepicker">
                                <input type="text" name="popupPeriodETimeT" value="<?=$data['popupPeriodETimeT']; ?>" class="form-control width-xs" placeholder="종료시간입력">
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
            <td colspan="3">
                <label class="checkbox-inline"><input type="checkbox" name="todayUnSeeFl" value="y" <?=gd_isset($checked['todayUnSeeFl']['y']); ?> />'오늘 하루 보이지 않음' 기능을 사용합니다.</label>

                <div id="todayUnSee" class="form-inline box_form">
                    <div>
                        배경 색상 :
                        <input type="text" name="todayUnSeeBgColor" value="<?=$data['todayUnSeeBgColor']; ?>" readonly class="form-control width-xs center color-selector"/>
                    </div>
                    <div>
                        글자 색상 :
                        <input type="text" name="todayUnSeeFontColor" value="<?=$data['todayUnSeeFontColor']; ?>" readonly class="form-control width-xs center color-selector"/>
                    </div>
                    <div>
                        정렬 :
                        <label class="radio-inline"><input type="radio" name="todayUnSeeAlign" value="left" <?=gd_isset($checked['todayUnSeeAlign']['left']); ?> /> 왼쪽</label>
                        <label class="radio-inline"><input type="radio" name="todayUnSeeAlign" value="center" <?=gd_isset($checked['todayUnSeeAlign']['center']); ?> /> 가운데</label>
                        <label class="radio-inline"><input type="radio" name="todayUnSeeAlign" value="right" <?=gd_isset($checked['todayUnSeeAlign']['right']); ?> /> 오른쪽</label>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>이미지 이동방법</th>
            <td colspan="3">
                <div class="form-inline">
                    <select name="popupSlideDirection" class="form-control eng">
                        <?php
                        foreach ($getPopupSlideDirection as $pKey => $pVal) {
                            echo '<option value="' . $pKey . '" ' . gd_isset($selected['popupSlideDirection'][$pKey]) . '>' . $pVal . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <th>이미지 이동속도</th>
            <td colspan="3">
                <div class="form-inline">
                    <select name="popupSlideSpeed" class="form-control eng">
                        <?php
                        for ($i =1; $i < 10; $i++ ) {
                            echo '<option value="' . $i . '" ' . gd_isset($selected['popupSlideSpeed'][$i]) . '>' . $i . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <th>이미지 개수</th>
            <td colspan="3">
                <div class="form-inline">

                        <?php
                        foreach ($getPopupSlideCount as $pKey => $pVal) {
                            echo '<label class="radio-inline"><input type="radio" name="popupSlideCount" value="' . $pKey . '" ' . gd_isset($checked['popupSlideCount'][$pKey]) . '>' . $pVal.'</label>' ;
                        }
                        ?>

                </div>
            </td>
        </tr>
        <tr>
            <th class="require">큰 이미지 크기</th>
            <td>
                <div class="form-inline">
                    <label>가로크기 : <input type="text" name="popupSlideViewW" value="<?=$data['popupSlideViewW']; ?>" class="form-control js-number width-2xs center"  /> pixel</label>
                    <br/>
                    <label>세로크기 : <input type="text" name="popupSlideViewH" value="<?=$data['popupSlideViewH']; ?>"class="form-control js-number width-2xs center" " /> pixel</label>
                </div>
            </td>
            <th>작은 이미지 크기</th>
            <td>
                <div class="form-inline">
                    <label>가로크기 : <input type="text" name="popupSlideThumbW" value="<?=$data['popupSlideThumbW']; ?>" class="form-control js-number width-2xs center" disabled="disabled"/> pixel</label><br/>
                    <label>세로크기 : <input type="text" name="popupSlideThumbH" value="<?=$data['popupSlideThumbH']; ?>" class="form-control js-number width-2xs center"/> pixel</label>
                </div>
            </td>
        </tr>
        <tr>
            <th>멀티팝업 노출 페이지</th>
            <td colspan="3">
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
        <tr>
            <th>팝업 이미지
            <input type="button" value="미리보기" class="btn btn-white btn-sm" onclick="multi_popup_preview()" />
            </th>
            <td colspan="3">
                <table class="table table-cols js-multi-popup-preview" style="width:400px;border: 1px solid #E6E6E6;">
                    <thead>
                    <tr>
                        <td colspan="<?=$data['popupSlideCountWidth']?>" style="height:300px;"></td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php for($i = 0; $i < $data['popupSlideCountHeight']; $i++) { ?>
                    <tr>
                        <?php for($j = 0; $j < $data['popupSlideCountWidth']; $j++) {
                            $sno = $j+($i*$data['popupSlideCountWidth']);
                            ?>
                        <td class="center js-thumb-image-<?=$sno?>" style="height:100px;background:#fff;"><input type="button" value="파일선택"  class="btn btn-white btn-sm" data-image-sno="<?=$sno?>" /></td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <?php if($mode =='modify' && $data['image']) {
        foreach($data['image'] as $k => $v) { ?>
            <div id="lay_image_<?=$k?>">
            <input type="hidden" name="image[<?=$k?>][imageUploadFl]" value="<?=$v['imageUploadFl']?>"/>
            <input type="hidden" name="image[<?=$k?>][thumbImage1]" value="<?=$v['thumbImage1']?>"/>
            <input type="hidden" name="image[<?=$k?>][thumbImage2]" value="<?=$v['thumbImage2']?>"/>
            <input type="hidden" name="image[<?=$k?>][mainImage]" value="<?=$v['mainImage']?>"/>
            <input type="hidden" name="image[<?=$k?>][imageLinkUrl]" value="<?=$v['imageLinkUrl']?>"/>
            <input type="hidden" name="image[<?=$k?>][imageLinkTarget]" value="<?=$v['imageLinkTarget']?>"/>
            </div>
    <?php  } } ?>
</form>


<script type="text/html" id="lay_image_form">

<input type="hidden" name="image[<%=sno%>][tmpFl]" value="y"/>
<input type="hidden" name="image[<%=sno%>][imageUploadFl]" value="<%=imageUploadFl%>"/>
<input type="hidden" name="image[<%=sno%>][thumbImage1]" value="<%=thumbImage1%>"/>
<input type="hidden" name="image[<%=sno%>][thumbImage2]" value="<%=thumbImage2%>"/>
<input type="hidden" name="image[<%=sno%>][mainImage]" value="<%=mainImage%>"/>
<input type="hidden" name="image[<%=sno%>][imageLinkUrl]" value="<%=imageLinkUrl%>"/>
<input type="hidden" name="image[<%=sno%>][imageLinkTarget]" value="<%=imageLinkTarget%>"/>

</script>

<script type="text/html" id="lay_image_upload">
    <form  name="frmImage<%=sno%>" method="post" action="./multi_popup_ps.php" enctype="multipart/form-data" target="ifrmProcess">
        <input type="hidden" name="mode" value="image">
        <input type="hidden" name="sno" value="<%=sno%>">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>

            <tr>
                <th>이미지등록방식</th>
                <td><label><input type="radio" name="imageUploadFl"  value="y"  checked='checked' onclick="image_select(this.value)"/>직접 업로드</label>
                    <label><input type="radio" name="imageUploadFl"  value="n"   onclick="image_select(this.value)" />이미지호스팅 URL입력</label>
                </td>
            </tr>
            <tbody class="js-image-upload">
            <tr>
                <th>작은이미지1</th>
                <td> <div class="form-inline"><input type="file" name="thumbImage1" class="form-control width-lg"/>
                    <span class="preview-thumb-image1"></span><input type="hidden" name="preview[thumbImage1]" value="<%=thumbImage1%>" /></div>
                </td>
            </tr>
            <tr>
                <th>작은이미지2</th>
                <td> <div class="form-inline"><input type="file"  name="thumbImage2" class="form-control width-lg"/>
                    <span class="preview-thumb-image2"></span><input type="hidden" name="preview[thumbImage2]" value="<%=thumbImage2%>"  /></div>
                </td>
            </tr>
            <tr>
                <th>큰이미지</th>
                <td> <div class="form-inline"><input type="file"  name="mainImage" class="form-control width-lg"/>
                    <span class="preview-main-image"></span><input type="hidden" name="preview[mainImage]" value="<%=mainImage%>"  /></div>
                </td>
            </tr>
            </tbody>
            <tbody class="js-image-text" style="display:none">
            <tr>
                <th>작은이미지1</th>
                <td><input type="text" name="thumbImage1" class="form-control width-lg"  value="<%=thumbImage1%>" />  </td>
            </tr>
            <tr>
                <th>작은이미지2</th>
                <td><input type="text"  name="thumbImage2" class="form-control width-lg"  value="<%=thumbImage2%>"/>  </td>
            </tr>
            <tr>
                <th>큰이미지</th>
                <td><input type="text"  name="mainImage" class="form-control width-lg"  value="<%=mainImage%>" />  </td>
            </tr>
            </tbody>
            <tr>
                <th>링크주소</th>
                <td> <div class="form-inline"><input type="text" name="imageLinkUrl" class="form-control width-lg"   value="<%=imageLinkUrl%>"/>
                    <select name="imageLinkTarget">
                            <option value="_self">현재창</option>
                        <option value="_blank">새창</option>
                    </select></div>

                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="notice-info">외부사이트(절대경로) 링크주소 입력 시 'http://'를 입력해야 합니다.</span><br/>
                    <span class="notice-info">내부사이트(상대경로) 링크주소 입력 시 'http://'를 제외하고 입력해야 합니다.</span><br/>
                </td>
            </tr>
        </table>



        <div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black" onclick="submit_image(this.form);"></div>
    </form>
</script>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

        <?php if($mode =='modify' && $data['image']) { ?>
        if($("input[name='image[0][imageUploadFl]']").val() =='n')  var mainImage = $("input[name='image[0][mainImage]']").val();
        else  var mainImage = "<?=$data['imagePath']?>"+$("input[name='image[0][mainImage]']").val();


        $('.js-multi-popup-preview thead td').css({"background":"url("+mainImage+")", 'background-repeat' : 'no-repeat', 'background-position':'center center'});

        <?php foreach($data['image'] as $k => $v) {
        if($v['imageUploadFl'] =='n') $imagePath = "";
        else $imagePath = $data['imagePath'];
        ?>
        $('.js-thumb-image-<?=$k?>').css({"background":"url(<?=$imagePath?>"+$("input[name='image[<?=$k?>][thumbImage1]']").val()+")", 'background-repeat' : 'no-repeat', 'background-position':'center center'});
        <?php } ?>
        <?php } ?>

        var frmObj = $('#frmMultiPopup');
        var selectArea = "0";

        // 멀티팝업 정보 저장
        frmObj.validate({
            submitHandler: function (form) {



                if(parseInt($("input[name='popupSlideViewW']").val()) <= 0 || parseInt($("input[name='popupSlideViewH']").val()) <= 0 || parseInt($("input[name='popupSlideThumbH']").val()) <= 0) {
                    alert("이미지크기를 확인해주세요.");
                    return false;
                }

                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                popupTitle: "required",
                popupSkin: "required",
                popupSlideViewW: "required",
                popupSlideViewH: "required",
                popupSlideThumbH: "required"
            },
            messages: {
                popupTitle: {
                    required: '멀티팝업 제목을 입력해 주세요.'
                },
                popupSkin: {
                    required: '멀티팝업창 스킨을 선택해 주세요.'
                },
                popupSlideViewW: {
                    required: '큰 이미지 가로크기를 입력해 주세요.'
                },
                popupSlideViewH: {
                    required: '큰 이미지 세로크기를 입력해 주세요.'
                },
                popupSlideThumbH: {
                    required: '작은 이미지 세로크기를 입력해 주세요.'
                },
            }
        });

        // 기간별 노출 설정 여부
        $('input[name=\'popupPeriodOutputFl\']', frmObj).each(setPopupPeriodOutputFl);
        $('input[name=\'popupPeriodOutputFl\']', frmObj).click(setPopupPeriodOutputFl);

        // 오늘 하루 보이지 않음 사용여부
        $('input[name=\'todayUnSeeFl\']', frmObj).each(setTodayUnSeeFl);
        $('input[name=\'todayUnSeeFl\']', frmObj).click(setTodayUnSeeFl);

        $("input[name='popupSlideCount']").click(function () {
            var widthCount = $(this).val().substr(0,1);
            var heightCount = $(this).val().substr(1,1);
            var addHtml = "";
            for(i = 0; i < heightCount ;i++ ) {
                addHtml +="<tr>";
                for(j = 0; j < widthCount ;j++ ) {
                    addHtml +="<td class='center js-thumb-image-"+(j+(i*widthCount))+"' style='height:100px;background:#fff;'><input type='button' value='파일선택'  class='btn btn-white btn-sm' data-image-sno ='"+(j+(i*widthCount))+"' /></td>";
                }
                addHtml +="</tr>";
            }


            $(".js-multi-popup-preview thead td").attr('colspan',widthCount);
            $(".js-multi-popup-preview thead td").css('background',"#fff");
            $(".js-multi-popup-preview tbody").html(addHtml);

            $("div[id*='lay_image_']").html('');

            if($("input[name='popupSlideViewW']").val() > 0) {
                $("input[name='popupSlideThumbW']").val(parseInt($("input[name='popupSlideViewW']").val()/widthCount));
            }


        });

        $("input[name='popupSlideViewW']").change(function () {
            var count = $("input[name='popupSlideCount']:checked").val().substr(0,1);
            $("input[name='popupSlideThumbW']").val(parseInt($(this).val()/count));
        });


        $('.js-multi-popup-preview').on('click', 'input:button', function (event) {

            var sno = $(this).data('image-sno');

            var addHtml="";
            var complied = _.template($('#lay_image_upload').html());

            addHtml += complied({
                sno: sno,
                thumbImage1: $("input[name='image["+sno+"][thumbImage1]']").val(),
                thumbImage2: $("input[name='image["+sno+"][thumbImage2]']").val(),
                mainImage: $("input[name='image["+sno+"][mainImage]']").val(),
                imageLinkUrl: $("input[name='image["+sno+"][imageLinkUrl]']").val()
            });

            layer_popup(addHtml, '멀티 팝업 이미지 등록');

            setTimeout(function(){
                if($("input[name='image["+sno+"][imageUploadFl]']").length) {
                    console.log($("input[name='image["+sno+"][imageUploadFl]']").val());
                    $("form[name='frmImage"+sno+"'] input[name='imageUploadFl'][value='"+$("input[name='image["+sno+"][imageUploadFl]']").val()+"']").click();
                }

                if($("input[name='image["+sno+"][imageLinkTarget]']").length) {

                    if($("input[name='image["+sno+"][imageUploadFl]']").val() =='n')  var imagePath = "";
                    else  var imagePath = "<?=$data['imagePath']?>";

                    $("form[name='frmImage"+sno+"'] select[name='imageLinkTarget']").val( $("input[name='image["+sno+"][imageLinkTarget]']").val());
                    if($("input[name='image["+sno+"][thumbImage1]']").val()) $("span.preview-thumb-image1").html("<img src='"+imagePath+$("input[name='image["+sno+"][thumbImage1]']").val()+"' width='40px'>'");
                    if($("input[name='image["+sno+"][thumbImage2]']").val()) $("span.preview-thumb-image2").html("<img src='"+imagePath+$("input[name='image["+sno+"][thumbImage2]']").val()+"' width='40px'>'");
                    if($("input[name='image["+sno+"][mainImage]']").val()) $("span.preview-main-image").html("<img src='"+imagePath+$("input[name='image["+sno+"][mainImage]']").val()+"' width='40px'>'");

                }
            }, 500);

        });

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

    });

    function multi_popup_preview() {

        var loadChk	= $('#multi_popup_preview').length;

        var count = $("input[name='popupSlideCount']:checked").val().substr(0,1);
        for(i = 0; i < count ; i++) {
            if($("input[name='image["+i+"][imageUploadFl]']").length ==0) {
                alert("팝업 이미지 등록 후 미리보기가 가능합니다.");
                return false;
            }
        }



        if($("input[name='popupSlideViewW']").val() > 0 && $("input[name='popupSlideViewH']").val() > 0 && $("input[name='popupSlideThumbH']").val() > 0) {

            var formData = $("#frmMultiPopup").serialize();

            $.post('../design/multi_popup_preview.php',formData, function(data){
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

        } else {
            alert("큰 이미지의 가로크기,세로크기, 작은이미지의 세로 크기를 입력해주세요.");
        }

    }

    function image_select(val) {
        if(val == 'y') {
            $(".js-image-text").hide();
            $(".js-image-upload").show();
        } else {
            $(".js-image-upload").hide();
            $(".js-image-text").show();
        }
    }
    function submit_image(frm) {

        fd = new FormData(frm);

        $.ajax(
            'multi_popup_ps.php',
            {
                type: 'post',
                processData: false,
                contentType: false,
                data: fd,
                dataType: "json",
                success: function(data) {
                    if(data.imageUploadFl =='n')  data.imagePath = "";


                    $('.js-thumb-image-'+data.sno).css({"background":"url("+data.imagePath+data.thumbImage1+")", 'background-repeat' : 'no-repeat', 'background-position':'center center'});
                    $('.js-multi-popup-preview thead td').css({"background":"url("+data.imagePath+data.mainImage+")", 'background-repeat' : 'no-repeat', 'background-position':'center center'});

                    var addHtml="";
                    var complied = _.template($('#lay_image_form').html());
                    addHtml += complied({
                        sno:data.sno,
                        imageUploadFl:data.imageUploadFl,
                        thumbImage1: data.thumbImage1,
                        thumbImage2: data.thumbImage2,
                        mainImage: data.mainImage,
                        imageLinkUrl: data.imageLinkUrl,
                        imageLinkTarget: data.imageLinkTarget
                    });

                    if($("#lay_image_"+data.sno).length) {
                        $("#lay_image_"+data.sno).html(addHtml);
                    } else {
                        $("#frmMultiPopup").append("<div id='lay_image_"+data.sno+"' >"+addHtml+"</div>");
                    }

                    $('div.bootstrap-dialog-close-button').click();

                },
                error: function() {
                    console.log( "ERROR" );
                }
            });
        return false;

    }

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

