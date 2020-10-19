<form name="registForm" id="registForm" method="post">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <?php if($paycoSearchSetMode != 'request' && $paycoSearchPrintMode != 'wait-T'  && $paycoSearchPrintMode != 'wait-Y') { ?>
            <input type="submit" value="저장" class="btn btn-red"/>
        <?php } ?>
    </div>
    <div class="table-title gd-help-manual">
        페이코 서치 사용 설정
    </div>
    <input type="hidden" name="status" id="status" value="<?php echo $status; ?>" />
    <input type="hidden" name="mode" id="mode" value="configSearch" />
    <?php if ($paycoSearchSetMode == 'request') { ?>
        <div id="paycosearchRegistDiv" class="paycosearchDiv">
            <div class="panel pd10">
                <p><b>페이코 서치란?</b></p>
                <p>
                    NHN에서 개발한 고품질의 검색 결과를 제공하는 국내 유일의 프리미엄 검색 서비스입니다.<br />
                    페이코 서치는 국내 포털 사이트 80억 건 이상의 대용량 콘텐츠 검색 운영 경험을 바탕으로 안정적인 검색 서비스를 제공합니다.<br />
                    페이코 서치를 사용하면 쇼핑몰에 상품이 100만 개 이상 등록이 되어있어도 약 0.5초 안에 검색이 될 만큼 빠른 검색 서비스를 제공합니다. <a href="./paycosearch_info.php" class="btn btn-gray btn-sm">서비스 자세히 보기</a>
                </p>
            </div>
            <div class="div_center p_top20">
                <div class="div_center_sub"><a href="#" class="btn btn-gray js-paycosearch-request-form">페이코 서치 사용 신청</a></div>
            </div>
        </div>
    <?php } else { ?>
        <div id="paycosearchUseDiv" class="paycosearchDiv">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th>사용설정</th>
                    <td>
                        <div class="paycosearchDiv_NY useDiv">
                            <div <?php echo $displayUseyn; ?>>
                                <label class="radio-inline">
                                    <input type="radio" name="paycosearchFl" id="paycosearchFlY" value="Y" <?php echo $disabledUseyn; ?> <?php echo $disabledUsey; ?> <?php echo $shopData['checked']['paycosearchFl']['Y']; ?>>
                                    실사용하기
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="paycosearchFl" id="paycosearchFlN" value="N" <?php echo $shopData['checked']['paycosearchFl']['N']; ?>>
                                    사용안함
                                </label>
                            </div>
                            <div class="notice-danger onlyY only">
                                <?php if ($paycoSearchPrintMode == 'wait-T') { ?>
                                    <div>※ 페이코 서치 사용 준비 중 입니다. 최대 1일이 소요됩니다.</div>
                                <?php } elseif ($paycoSearchPrintMode == 'wait-Y') { ?>
                                    <div>※ 페이코 서치 사용 준비 중 입니다. 최대 1일이 소요됩니다.</div>
                                    <?php
                                } elseif ($paycoSearchPrintMode == 'wait-N') {
                                    if (empty($config['searchRejectMessage']) === false) {
                                        ?>
                                        <div>아래 사유로 페이코 서치 서비스 사용이 중지되었습니다.</div>
                                        <div><?php echo $config['searchRejectMessage']; ?></div>
                                    <?php } else { ?>
                                        <div>※ 페이코 서치 사용 중지 상태 값으로 변경 하셨습니다.</div>
                                        <?php
                                    }
                                } elseif ($paycoSearchPrintMode == 'setting-T') {
                                    ?>
                                    <div>'테스트하기' 선택 시에는 <a href="<?php echo URI_HOME . 'goods' . DS . 'goods_search.php'; ?>" target="_blank" class="paycoSearch_guide" style="font-weight: bold;text-decoration: underline;">[페이코 서치 적용 미리보기]</a>에만 페이코 서치가 적용 됩니다.</div>
                                    <div>반드시 페이코 서치 적용 미리보기에서 상품이 검색되는 것을 확인 후 '실사용하기' 설정을 해주세요.</div>
                                    <div>'실사용하기' 설정 시 구매자가 검색을 하게 되면 페이코 서치를 이용하여 상품을 검색 합니다.</div>
                                <?php } elseif ($paycoSearchPrintMode == 'setting-Y') { ?>
                                    <div>페이코 서치의 사용 준비가 되었습니다. 사용 설정 시 구매자가 검색을 하게 되면 페이코 서치를 이용하여 상품을 검색합니다.</div>
                                    <?php
                                } elseif ($paycoSearchPrintMode == 'setting-N') {
                                    if (empty($config['searchRejectMessage']) === false) {
                                        ?>
                                        <div>아래 사유로 페이코 서치 서비스 사용이 중지되었습니다.</div>
                                        <div><?php echo $config['searchRejectMessage']; ?></div>
                                    <?php } else { ?>
                                        <div>※ 페이코 서치 사용 중지 상태 값으로 변경 하셨습니다.</div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <div class="notice-danger mgb10 onlyY only">
                                '실사용하기' 설정 시 상품 색인 완료 후 페이코 서치가 자동 적용됩니다.<br />
                                사용 설정 시 구매자가 검색을 하게 되면 페이코 서치를 이용하여 상품을 검색합니다.<br />
                                적용 소요 시간은 최대 1일이며, 적용전까지 내부검색으로 검색됩니다.
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>사용 신청 정보</th>
                    <td>
                        <div><span class="bold">쇼핑몰URL:</span> <?=$shopData['dispDomain']?></div>
                        <div><span class="bold">쇼핑몰이름:</span> <?=$shopData['shopName']?></div>
                        <div><span class="bold">상품DB URL:</span> <?=$shopData['pipLink']?></div>
                        <?php if($paycoSearchPrintMode == 'wait-T' || $paycoSearchPrintMode == 'setting-T' || $paycoSearchPrintMode == 'setting-Y') { ?>
                            <div class="notice-info mgb10 blue">
                                페이코서치를 다른 URL로 사용을 원하시는 경우 <a class="hand bold againRegist" target="_blank">[페이코 서치 재신청]</a> 을 클릭하여 재신청 해주시기 바랍니다.
                            </div>
                        <?php } else if($paycoSearchPrintMode == 'wait-N' || $paycoSearchPrintMode == 'setting-N') { ?>
                            <div class="notice-info mgb10 blue">
                                페이코서치를 다른 URL로 사용을 원하시는 경우 <a class="hand bold againRegist" target="_blank">[페이코 서치 재신청]</a> 을 클릭하여 재신청 해주시기 바랍니다.
                            </div>
                        <?php } ?>
                        <div class="notice-info black">
                            기존에 등록된 쇼핑몰URL 및 이름 정보를 변경하고 싶으신 경우 <span class="blue"> 사용설정을 ‘사용안함’으로 변경 후 <a href="https://www.godo.co.kr/mygodo/helper_write.html" target="_blank" class="btn-link"> 1:1문의하기</a>로 변경 신청</span> 해주시기 바랍니다.
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>상품 데이터 생성 방법 설정</th>
                    <td>
                        <div class="mgb10">
                            <input type="radio" name="createType" id="typeAuto" value="auto" <?php echo $shopData['checked']['createType']['auto']; ?> /><label for="typeAuto">자동생성</label>
                            <input type="radio" name="createType" id="typeHand" value="hand" <?php echo $shopData['checked']['createType']['hand']; ?> /><label for="typeHand">수동생성</label>
                            <?php if ($config['createType'] == 'auto') { ?>
                                <span class="mgl10 notice-danger">수동생성 선택 후 저장 시 상품 데이터가 즉시 생성됩니다.</span>
                            <?php } ?>
                        </div>
                        <?php if ($config['createType'] == 'hand') { ?>
                            <div class="mgb10 option-hand">
                                <a href="#" class="btn btn-gray btn-make-pip">상품 데이터 생성</a> <span class="notice-danger">상품 데이터 수동생성은 1시간에 1번만 가능합니다.</span>
                            </div>
                        <?php } ?>
                        <div class="notice-info">페이코 서치로 검색할 수 있도록 상품 데이터를 생성하는 방법을 설정합니다.</div>
                        <div class="notice-info">자동생성으로 설정 시 1일 4회 생성 및 수집되며, 수동생성으로 설정 시에는 [상품 데이터 생성] 버튼 클릭 시 생성됩니다.(수집시간은 자동생성과 동일하게 1일 4회입니다.)</div>
                        <div class="notice-info">수동생성 설정 시 자동생성은 실행되지 않습니다.</div>
                    </td>
                </tr>
                <tr>
                    <th>자동완성 기능 사용</th>
                    <td>
                        <div class="mgb10">
                            <input type="radio" name="autocompleteFl" id="autocompleteY" value="Y" <?php echo $shopData['checked']['autocomplete']['Y']; ?> <?php echo $shopData['disabled']['autocomplete']['Y']; ?> /><label for="autocompleteY">사용함</label>
                            <input type="radio" name="autocompleteFl" id="autocompleteN" value="N" <?php echo $shopData['checked']['autocomplete']['N']; ?> /><label for="autocompleteN">사용안함</label>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div>
            <div class="notice-info mgb10 black">
                <span class="bold red">필독! 사용전 주의사항</span><br />
                신청 후 사용까지는 최대 1일이 소요됩니다.<br />
                페이코 서치 적용은 상품 색인 후 자동으로 이뤄지며, 자동 적용 전까지 내부검색이 적용됩니다.<br />
                쇼핑몰에 등록된 상품 데이터는 1일 4회 페이코 서치에 수집되며, 따라서 상품정보 변경 시 검색 페이지에 노출되는 상품정보가 반영될 때까지 일정 시간이 소요됩니다.<br />
                <span class="red bold">상품 이미지 수정 시에도 반영될 때까지 일정 시간이 소요되며, 상품정보가 반영되기 전에는 수정된 상품의 이미지가 출력되지 않을 수 있습니다.</span>
            </div>

            <div class="notice-info mgb10 black">
                <span class="bold">페이코 서치 사용 시 주의사항</span><br />
                1. 페이코 서치 사용 시 검색페이지 상품진열 및 PC, 모바일 쇼핑몰 테마선택 > 리스트 영역 상세 설정 일부 항목(짧은설명, 쿠폰가, 상품할인금액)은 반영되지 않습니다.<br />
                2. 페이코 서치는 통합검색만 사용하실 수 있습니다.
            </div>

            <?php if ($paycoSearchPrintMode == 'wait-T' || $paycoSearchPrintMode == 'wait-N') { ?>
                <div class="notice-info mgb10 black paycosearchDiv_T">
                    <span class="bold">사용 준비 중이란?</span><br />
                    사용 설정이 페이코 서치 사용 준비 중인 경우 검색 상품 DB를 생성하고 있는 단계입니다.<br />
                    페이코 서치 실사용 중에도 페이코 서치 사용 준비 중 안내가 출력될 경우 마이고도 ><a href="https://www.godo.co.kr/mygodo/helper_write.html" target="_blank" class="btn-link"> 1:1 문의하기 </a>로 문의하여 주시기 바랍니다.
                </div>
            <?php } ?>

            <div class="notice-info mgb10 black">
                <span class="bold">자동완성 기능이란?</span><br />
                사용자가 검색창에 입력한 키워드를 토대로, 기존 사용자들이 검색한 결과와 일치하는 검색키워드를 자동으로 완성하여 노출 시켜주는 기능입니다.<br />
                사용자 검색값이 적을 경우, 페이코 서치에 수집된 상품정보(카테고리, 브랜드, 검색키워드)가 대체되어 노출됩니다.<br />
                ※ 2017년 09월 20일 이전 제작 스킨을 사용하시는 경우 반드시 스킨패치를 적용해야 기능 사용이 가능합니다.
                <a href="https://www.godo.co.kr/customer/patch-view.gd?sno=3133
" target="_blank" class="btn-link">패치 바로가기&gt;</a>
            </div>
        </div>
    <?php } ?>
</form>
<style rel="stylesheet" type="text/css">
    .black { color:#000; }
    .red { color:#fa2828; }
    .blue { color:#117ef9; }
    .panel_center { margin:5px 0; text-align:center; }
    .div_center { width:100%; }
    .div_center_sub { margin:0 auto;text-align:center; }
    .p_top20 { padding-top:20px; }
    .p_1_4 { padding: 1px 4px; }
</style>
<script type="text/javascript">
    $(function () {
        $('input[name="paycosearchFl"]').click(function() {
            var value = this.value;

            $('#autocompleteY').removeAttr("disabled");
            switch (value) {
                case 'T':
                    break;
                case 'Y':
                    if (confirm('페이코 서치를 재사용 신청의 경우 최대 1일 동안 내부검색 후 페이코 서치가 자동 적용됩니다.\n계속하시겠습니까?')) {
                        $('input[name=createType]').attr('disabled', false);
                        $('#typeAuto').prop('checked', true);
                        $('input[name=autocompleteFl]').attr('disabled', false);
                        window.open('<?php echo URI_HOME . DS . 'goods' . DS . 'goods_search.php'; ?>', '_blank', '');
                    } else {
                        $('#paycosearchFlN').prop('checked', true);
                        $('#autocompleteN').prop('checked', true);
                        $('input[name=createType]').attr('disabled', true);
                        $('input[name=autocompleteFl]').attr('disabled', true);
                    }
                    break;
                case 'N':
                    if (confirm('사용안함으로 설정 시 검색 상품DB가 삭제되므로 영구적으로 사용하지 않을 경우에만 해당 설정을 해주시기 바랍니다.\n계속하겠습니까?')) {
                        $('#paycosearchFlN').prop('checked', true);
                        $('#autocompleteN').prop('checked', true);
                        $('input[name=createType]').attr('disabled', true);
                        $('input[name=autocompleteFl]').attr('disabled', true);
                    } else {
                        $('#paycosearchFlY').prop('checked', true);
                        $('input[name=createType]').attr('disabled', false);
                        $('input[name=autocompleteFl]').attr('disabled', false);
                    }
                    break;
            }
        });

        $(".js-paycosearch-request-form, .againRegist").click(function (e){
            e.preventDefault();
            var loadCheck = 0;
            $.ajax({
                url: '../policy/layer_paycosearch_regist.php',
                type: 'get',
                async: false,
                success: function (data) {
                    if(loadCheck == 0) {
                        data = '<div id="layerPaycosearch">' + data + '</div>';
                    }
                    BootstrapDialog.show({
                        title: '페이코서치 신청',
                        size: BootstrapDialog.SIZE_WIDE,
                        message: $(data),
                        closable: false
                    });
                },
                error: function (e) {
                    console.log(e);
                    alert('데이터를 불러오는데 실패했습니다. 다시 시도해주세요.');
                }
            });
        });

        $('.btn-make-pip').click(function(){
            $('input[name="mode"]').val('makePip');
            $("#registForm").submit();
        });
    });

    $("#registForm").validate({
        dialog: false,
        ignore: '.ignore',
        submitHandler: function (form) {
            var params = $(form).serializeArray();
            var $ajax = $.ajax('paycosearch_ps.php', {
                data: params,
                method: "POST"
            });
            $ajax.done(function (response) {
                dialog_alert(response.message, '확인', {
                    callback: function () {
                        if (_.isUndefined(response.error)) {
                            location.reload();
                        }
                    }
                });
            });
        }
    });
</script>
