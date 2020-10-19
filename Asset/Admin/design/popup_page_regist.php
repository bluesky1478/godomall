<form name="frmPopupPage" id="frmPopupPage" method="post" action="./popup_ps.php">
    <input type="hidden" name="mode" value="popupPageRegist"/>
    <input type="hidden" name="sno" value="<?php echo $data['sno']; ?>"/>
    <div class="page-header">
        <div class="page-header-title"><h3>노출위치 관리</h3></div>
        <div class="page-header-regist">
            <input type="button" value="노출위치 등록" class="btn btn-red-line btn-popup-page-regist">
            <input type="submit" value="저장" class="btn btn-red btn-popup-page-save">
        </div>
    </div>

    <div>
        <div class="table-title">노출위치 등록</div>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-xs"/>
            <col/>
        </colgroup>
        <thead>
        <tr>
            <th>구분</th>
            <th>항목명</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th class="require">구분</th>
            <td>
                <label class="checkbox-inline"><input type="checkbox" name="pcDisplayFl" value="y" <?php echo $checked['pcDisplayFl']['y']?> />PC 쇼핑몰</label>
                <label class="checkbox-inline"><input type="checkbox" name="mobileDisplayFl" value="y" <?php echo $checked['mobileDisplayFl']['y']?> />모바일 쇼핑몰</label>
            </td>
        </tr>
        <tr>
            <th class="require">페이지명</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="pageName" value="<?php echo $data['pageName']; ?>" class="form-control width-2xl"/>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">URL</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="pageUrl" value="<?php echo $data['pageUrl']; ?>" class="form-control width-2xl"/>
                    <p class="notice-info">
                        URL 등록 시 절대 경로가 아닌 상대 경로로 등록하시기 바랍니다.<br />
                        절대경로 : godomall.godomall.com/goods/goods_view.php<br />
                        상대경로 : goods/goods_view.php
                    </p>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    $(function(){
        $("#frmPopupPage").validate({
            dialog: false,
            submitHandler: function (form) {
                if ($('#frmPopupPage input[name="pcDisplayFl"]').is(':checked') === false && $('#frmPopupPage input[name="mobileDisplayFl"]').is(':checked') === false) {
                    alert('구분을 선택해주세요.');
                    return false;
                }

                var parameter = $('#frmPopupPage').serialize();
                $.ajax({
                    method: 'post',
                    cache: false,
                    url: form.action,
                    data: parameter,
                    success: function(data){
                        data = $.parseJSON(data);

                        $.get('./popup_page_regist.php', {'layerFormID': 'popupPageForm', 'mode': 'simple', 'sno': data.pageResetNo}, function(data){
                            $('#popup-page-regist-area').html(data);
                        });

                        $.get('./popup_page_list.php', {'layerFormID': 'popupPageForm', 'mode': 'simple', 'reloadDisplay': 1}, function(data){
                            $('#popup-page-list-area').html(data);
                        });

                        var html = '<option value="' + data.pageUrl + '" data-sno="' + data.sno + '">' + data.pageName + ' : ' + data.pageUrl + '</option>';

                        if (data.pcDisplayFl == 'y') {
                            if ($('select[name="popupPageUrl"]').find('option[data-sno="' + data.sno + '"]').length > 0) {
                                $('select[name="popupPageUrl"]').find('option[data-sno="' + data.sno + '"]').val(data.pageUrl).html(data.pageName + ' : ' + data.pageUrl);
                            } else{
                                $('select[name="popupPageUrl"]').append(html);
                            }
                        }else {
                            if ($('select[name="popupPageUrl"]').find('option[data-sno="' + data.sno + '"]').length > 0) {
                                $('select[name="popupPageUrl"]').find('option[data-sno="' + data.sno + '"]').remove();
                            }
                        }

                        if (data.mobileDisplayFl == 'y') {
                            if ($('select[name="mobilePopupPageUrl"]').find('option[data-sno="' + data.sno + '"]').length > 0) {
                                $('select[name="mobilePopupPageUrl"]').find('option[data-sno="' + data.sno + '"]').val(data.pageUrl).html(data.pageName + ' : ' + data.pageUrl);
                            } else{
                                $('select[name="mobilePopupPageUrl"]').append(html);
                            }
                        } else {
                            if ($('select[name="mobilePopupPageUrl"]').find('option[data-sno="' + data.sno + '"]').length > 0) {
                                $('select[name="mobilePopupPageUrl"]').find('option[data-sno="' + data.sno + '"]').remove();
                            }
                        }
                    }
                });
            },
            rules: {
                'pageName': 'required',
                'pageUrl': 'required'
            },
            messages: {
                'pageName': {
                    required: '페이지명을 입력해주세요'
                },
                'pageUrl': {
                    required: 'URL을 입력해주세요'
                }
            }
        });
    });
</script>
