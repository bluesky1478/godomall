<form id="layerForm" name="layerForm" action="" method="post">
    <input type="hidden" name="mode" value="<?php echo $mode; ?>" />
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>대표 URL 선택</th>
            <td>
                <select name="searchAllDomain" id="searchAllDomain">
                    <option value="">= 쇼핑몰 대표 도메인을 선택해주세요 =</option>
                    <?php
                    foreach($domainList as $domainKey => $domainValue) {
                        $domainUserSplit = explode('|', $domainList[$domainKey]);
                        ?>
                        <option name="<?php echo $domainUserSplit[0]; ?>" value="<?php echo $domainList[$domainKey]; ?>" <?php echo $paycosearchConfig['searchDisplayDomain'] == $domainUserSplit[1] ? 'selected' : ''; ?>><?php echo $domainUserSplit[1]; ?><?php echo $paycosearchConfig['searchDisplayDomain'] == $domainUserSplit[1] ? ' [사용중인 URL]' : '' ?></option>
                    <?php } ?>
                </select>
                <div>대표 URL은 고도몰 홈페이지 > 마이고도 > 쇼핑몰 관리에서 신청하실 수 있습니다.</div>
            </td>
        </tr>
        <tr>
            <th>페이코 서치<br />상품 DB URL</th>
            <td>
                http://<span id="shopDomainReplace"><?php echo $paycosearchConfig['searchDisplayDomain']; ?></span><?php echo $paycosearchConfig['pipDbUrlDir']; ?>
            </td>
        </tr>
        <tr>
            <th>쇼핑몰 이름</th>
            <td>
                <input type="text" name="shopName" id="shopName" value="<?php echo $paycosearchConfig['searchShopName']; ?>" placeholder="예) 고도몰" class="form-control width-lg"/>
            </td>
        </tr>
    </table>
    <div id="paycoAgreement" class="form-inline panel pd10 term-scroll">
        <?php echo nl2br(htmlspecialchars($terms)); ?>
    </div>
    <div class="form-inline center">
        <label class="checkbox-inline mgb10">
            <input type="checkbox" name="agreementFlag" value="y"/>
            위 내용에 모두 동의합니다.
        </label>
    </div>
    <div class="text-center btn-box">
        <button type="button" class="btn btn-lg btn-black" id="layerBtnConfirm">사용신청</button>
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
    </div>
</form>
<style rel="stylesheet" type="text/css">
    .term-scroll { height:300px; overflow-x:hidden; overflow-y:auto; word-break:break-all; }
</style>
<script type="text/javascript">
    $(document).ready(function () {
        $("#searchAllDomain").change(function () {
            var domainValue = $("#searchAllDomain").val();
            var domainValueSplit = domainValue.split('|');
            if(!domainValueSplit[1]) {
                domainValueSplit[1] = '';
            }
            $("#shopDomainReplace").html(domainValueSplit[1]);
        });
        $("#layerForm").validate({
            dialog: false,
            ignore: '.ignore',
            rules: {
                searchAllDomain: {
                    required: true
                },
                shopName: {
                    required: true
                },
                agreementFlag: {
                    required: true
                },
            },
            messages: {
                searchAllDomain: {
                    required: "쇼핑몰 대표 URL을 선택 해주세요."
                },
                shopName: {
                    required: "쇼핑몰 이름을 입력해주세요."
                },
                agreementFlag: {
                    required: "페이코 서치 이용약관에 동의해주세요."
                }
            },
            submitHandler: function (form) {
                if (confirm('신청정보를 변경하여 재신청하게 되면\n페이코 서치를 준비하는 동안(최대1일) 기존 검색 페이지로 적용됩니다.\n계속하시겠습니까?')) {
                    var params = $(form).serializeArray();
                    $.ajax({
                        url: 'paycosearch_ps.php',
                        data: params,
                        method: "POST",
                        beforeSend: function() {
                            var html = '<div class="js-dim-layer" style="position:absolute;width:100%;height:100%;top:0px;left:0px;z-index:1051;"><img src="<?php echo UserFilePath::adminSkin("gd_share", "img", "paycosearch")->www(); ?>/icon_loading01.gif" class="loading-img" style="position:absolute;" /></div>';
                            $('body').append(html).find('.loading-img').center();
                            $('.js-dim-layer').width($('#container-wrap').width()).height($('body').prop('scrollHeight'));
                        }
                    }).done(function (response) {
                        $('.js-dim-layer').remove();
                         dialog_alert(response.message, '확인', {
                         callback: function () {
                         $('.js-dim-layer').addClass('display-none');
                         location.reload();
                         }
                         });
                    });
                }
            }
        });
        $('#layerBtnConfirm').click(function () {
            $('#layerForm').submit();
        });
    });
</script>
