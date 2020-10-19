<form id="frmMilageAdd" name="frmMilageAdd" action="../board/plus_review_ps.php" method="post" target="ifrmProcess" class="content-form js-setup-form">
<table class="table table-rows">
    <colgroup>
        <col class="width-xm"/>
        <col/>
    </colgroup>
    <tr>
        <th>지급방법 &nbsp;&nbsp;&nbsp;</th>
        <td>
            <label class="radio-inline">
                <input name="milageAddWay" type="radio"
                       value='autoSet' checked/>
                설정된 마일리지 지급
            </label>
            <label class="radio-inline">
                <input name="milageAddWay" type="radio"
                       value='direct'/>
                직접 지급
            </label>
            <div class="notice-info">설정된 마일리지 지급 시 ‘플러스리뷰 게시판 설정 > 리뷰작성 혜택 설정’의 설정에 따라 지급됩니다.</div>
        </td>
    </tr>
    <tr class="js-mileage-tr-autoSet">
        <th>마일리지 지급<br>조건</th>
        <td>
            리뷰 작성 글자수 최소 <?= $data['mileageAddminLimit'] ?>자 이상 입력 시에만 지급
            <?php if ($data['authWriteExtra'] === 'buyer' && $data['mileageAddLimitGoodsPrice'] !== '') { ?>
            <br/>구매 상품 가격 <?= $data['mileageAddLimitGoodsPrice']; ?>원 이상인 상품 리뷰 등록 시 마일리지 지급
            <?php } ?>
        </td>
    </tr>
    <tr class="js-mileage-tr-autoSet">
        <th>마일리지 지급</th>
        <td>
            <div>
                플러스리뷰 작성 시 <?= $data['mileageAmount']['review'] ?>원 지급
            </div>
            <div> 포토리뷰 작성 시 <?= $data['mileageAmount']['photo'] ?>원 추가지급
            </div>
            <div>상품별 첫 리뷰 작성 시 <?= $data['mileageAmount']['first'] ?>원 추가지급
            </div>
        </td>
    </tr>
    <tr class="js-mileage-tr-direct">
        <th class="require">금액설정</th>
        <td>
            <span>(+)</span>
            <input type="text" name="mileageValue" value="" class="js-number" maxlength="8"/>
            원
        </td>
    </tr>
    <tr class="js-mileage-tr-direct">
        <th>회원안내</th>
        <td>
            <label class="checkbox-inline">
                <input type="checkbox" name="guideSend[]"
                       value="sms"
                />SMS발송
            </label>
            <a href="#member" class="btn-link js-link-sms-auto">상세설정 ></a>
            <label class="checkbox-inline">
                <input type="checkbox" name="guideSend[]"
                       value="email"
                />이메일발송
            </label>
            <a href="#point" class="btn-link js-link-mail-auto">상세설정 ></a>
            <div>
                <span class="notice-info">* SMS는 잔여포인트가 있어야 발송됩니다. (잔여포인트 :
                    <span class="text-darkred bold"><?= number_format(gd_get_sms_point(), 1); ?></span>
                    ) <a href="#" class="btn btn-xs btn-gray mgl10 js-link-sms-charge">SMS포인트 충전하기</a>
                </span>
            </div>
        </td>
    </tr>

</table>
<div class="center"><input type="button" value="처리" class="btn btn-red js-btn-milage-act"> <input type="button" value="취소" class="btn btn-gray" onclick="layer_close()"></div>
</form>

<script type="text/javascript">
    var reviewCount = <?=$mileageAmount['count']['review'] ?? 0;?>;
    var photoCount = <?=$mileageAmount['count']['photo'] ?? 0;?>;
    var firstCount = <?=$mileageAmount['count']['first'] ?? 0;?>;
    var reviewAmount = <?=$mileageAmount['amount']['review'] ?? 0;?>;
    var photoAmount = <?=$mileageAmount['amount']['photo'] ?? 0;?>;
    var firstAmount = <?=$mileageAmount['amount']['first'] ?? 0;?>;

    $(document).ready(function () {

        $(':radio[name=milageAddWay]').bind('click', function () {
            if ($(this).val() == 'direct') {
                $('.js-mileage-tr-direct').show();
                $('.js-mileage-tr-autoSet').hide();
            }
            else {
                $('.js-mileage-tr-autoSet').show();
                $('.js-mileage-tr-direct').hide();
            }
        });
        $(':radio[name=milageAddWay]:checked').trigger('click');

        $('.js-btn-milage-act').click(function (e) {
            if ( $(':radio[name=milageAddWay]:checked').val() == 'autoSet') {
                if (reviewAmount + photoAmount + firstAmount > 0) {
                    var msg = '아래와 같이 마일리지가 지급됩니다. 계속하시겠습니까?';
                    msg += '<br/>게시글 ' + reviewCount + '건에 대해 플러스리뷰 작성 시 ' + reviewAmount + '원 지급';
                    msg += '<br/>게시글 ' + photoCount + '건에 대해 포토리뷰 작성 시 ' + photoAmount + '원 추가지급';
                    msg += '<br/>게시글 ' + firstCount + '건에 대해 상품별 첫 리뷰 작성 시 ' + firstAmount + '원 추가지급';
                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_WARNING,
                        title: '마일리지 지급',
                        message: msg,
                        closable: false,
                        callback: function (result) {
                            if (result) {
                                giveMileage();
                            }
                        }
                    });
                } else {
                    BootstrapDialog.show({
                        title: '마일리지 지급',
                        message: '선택된 모든 게시글에 지급될 마일리지가 없습니다.',
                        closable: true,
                    });
                }
            } else {
                giveMileage();
            }
        });

    });

    var $js_link_sms_charge = $('.js-link-sms-charge');
    if ($js_link_sms_charge.length > 0) {
        $js_link_sms_charge.click(function (e) {
            window.open('../member/sms_charge.php?popupMode=yes', 'sms_charge', 'width=1400, height=700, scrollbars=no');
        });
    }

    var $js_link_sms_auto = $('.js-link-sms-auto');
    var hash = '';
    if ($js_link_sms_auto.length > 0) {
        $js_link_sms_auto.click(function (e) {
            if ($(this).attr('href')) {
                hash = $(this).attr('href');
            }
            window.open('../member/sms_auto.php?popupMode=yes' + hash, 'sms_auto', 'width=1400, height=700, scrollbars=no');
        });
    }

    var $js_link_mail_auto = $('.js-link-mail-auto');
    var hash = '';
    if ($js_link_mail_auto.length > 0) {
        $js_link_mail_auto.click(function (e) {
            if ($(this).attr('href')) {
                hash = $(this).attr('href');
            }
            window.open('../member/mail_config_auto.php?popupMode=yes' + hash, 'mail_auto', 'width=1400, height=700, scrollbars=no');
        });
    }

    function giveMileage() {
        var sno = '<?=$sno?>';
        var snoArry = sno.split(",");

        if( $(':radio[name=milageAddWay]:checked').val() == 'autoSet'){

            parameter = { mode: 'milageAdd', sno: snoArry , milageAddWay: 'autoSet'};

        }else{

            var mileageValue = $('input[name=\'mileageValue\']').val();
            var guideSendArry = [];
            var dialogMessage;
            var title;
            if(mileageValue == ''){
                alert('금액을 입력해주세요');
                return;
            };

            $(':checkbox[name=\'guideSend[]\']:checked').each(function() {
                guideSendArry.push($(this).val());
            });

            parameter = {
                mode: 'milageAdd',
                sno: snoArry,
                milageAddWay: 'direct',
                mileageValue: mileageValue,
                guideSend: guideSendArry
            };

        }

        layer_close();
        BootstrapDialog.show({
            message: '처리중'
        });

        ajax_with_layer('../board/plus_review_ps.php',parameter , function (data, textStatus, jqXHR) {
            layer_close();
            if (data[0]) {
                dialogMessage = "지급이 완료되었습니다. 처리된 내역을 확인하시겠습니까?";
                title = "마일리지 지급 처리 완료";

            } else {
                if(data[1]){
                    dialogMessage = data[1];
                }else{
                    dialogMessage = '마일리지 지급 처리 중 오류가 발생하였습니다.';
                }
                title = "마일리지 지급 오류";
            }
            BootstrapDialog.confirm({
                title: title,
                message: dialogMessage,
                btnOKLabel: "처리내역 확인",
                btnCancelLabel: "계속진행",
                callback: function (result) {
                    if (result) {
                        top.location.href = '../member/member_batch_mileage_list.php';
                    } else {
                        top.location.reload();
                    }
                }
            });
        });
    }
    //-->
</script>
