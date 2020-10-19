<form id="frmPayco" name="frmPayco" action="settle_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="payco_config"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>

        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <table class="table table-cols paycoInfo">
        <tr>
            <td class="payco_BgColorWhite">
                <strong>페이코 결제란?</strong><br/> NHN PAYCO에서 제공하는 결제대행 서비스입니다. 바로구매 서비스와 간편결제 서비스를 제공합니다.<br/>
                <table cellpadding="5" cellspacing="0" border="0" width="100%" class="mgt10">
                    <tr>
                        <td>
                            <table class="table table-cols sub_paycoInfo">
                                <colgroup>
                                    <col class="width-sm"/>
                                    <col/>
                                </colgroup>
                                <tr>
                                    <td width="70" class="payco_BgColorGray2 text-center">바로구매</td>
                                    <td class="lastTd">
                                        <div>- 페이코 ID로 상품주문(쇼핑몰 비회원 구매)</div>
                                        <div>- 페이코의 결제수단으로 주문</div>
                                        <div>- 신용카드, 계좌이체, 가상계좌, 휴대폰결제 지원</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table class="table table-cols sub_paycoInfo">
                                <colgroup>
                                    <col class="width-sm"/>
                                    <col/>
                                </colgroup>
                                <tr>
                                    <td width="70" class="payco_BgColorGray2 text-center">간편결제</td>
                                    <td class="lastTd">
                                        <div>- 쇼핑몰 ID나 비회원으로 구매</div>
                                        <div>- 기존의 결제수단과 함께 사용 가능</div>
                                        <div>- 신용카드, 휴대폰결제 지원</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p class="msg">자세한 내용은 서비스 안내를 참고하여 주세요.&nbsp;
                    <button type="button" class="btn btn-black btn-xs" onclick="window.open('/service/service_info.php?menu=pg_payco_info','_blank')">바로가기</button>
                </p>
            </td>
        </tr>
    </table>

    <div class="paycoTab">
        <div onclick="javascript:location.href='#part1';" style="cursor: pointer;">페이코 결제 연동 설정</div>
        <div onclick="javascript:location.href='#part2';" style="cursor: pointer;">페이코 결제 이용 설정</div>
        <div onclick="javascript:location.href='#part3';" style="cursor: pointer;">페이코 결제 상품 설정</div>
    </div>

    <div id="part1" class="table-title gd-help-manual">페이코 결제 연동 설정</div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <td class="payco_BgColorGray2">페이코 결제 선택</td>
            <td class="payco_BgColorWhite">
                <?php if ($paycoApproval === false) { ?>
                    <div class="notice-info notice-danger">페이코 결제 신청이 '승인 완료' 되지 않았습니다.</div>
                    <div class="notice-info">
                        '부가서비스 >  페이코 결제'에서 신규 신청을 하시거나 신청 승인 상태를 확인하시기 바랍니다.
                        <a href="https://www.godo.co.kr/echost/power/add/payment/payco-intro.gd" target="_blank" class="btn btn-gray btn-sm">페이코 결제 신청</a>
                    </div>
                <?php } ?>
                <div>
                    <label class="radio-inline">
                        <input type="radio" name="useType" value="CE" class="payco_borderZ" <?=$checked['useType']['CE'];?> /> 페이코 바로구매 + 페이코 간편결제
                    </label>
                    &nbsp;
                    <label class="radio-inline">
                        <input type="radio" name="useType" value="E" class="payco_borderZ" <?=$checked['useType']['E'];?> /> 페이코 간편결제
                    </label>
                    &nbsp;
                    <label class="radio-inline">
                        <input type="radio" name="useType" value="N" class="payco_borderZ" <?=$checked['useType']['N'];?> /> 사용안함
                    </label>
                </div>
                <?php if ($paycoApproval === true) { ?>
                <p class="notice-info">이용할 페이코 결제 종류를 선택해주세요.</p>
                <?php } ?>

                <div id="individualCustomNo" class="display-none" style="margin-top:10px; border-top:1px solid #E6E6E6;">
                    <table class="table table-cols payco_textInputLayout mgt10">
                        <tr>
                            <td class="firstTd" style="width:280px;"><strong>페이코 바로구매 개인통관 고유번호 사용</strong></td>
                            <td>
                                <label class="radio-inline">
                                    <input type="radio" name="individualCustomNoInputUseYn" value="y" class="payco_borderZ" <?=$checked['individualCustomNoInputUseYn']['y'];?> /> 사용함
                                </label>
                                &nbsp;
                                <label class="radio-inline">
                                    <input type="radio" name="individualCustomNoInputUseYn" value="n" class="payco_borderZ" <?=$checked['individualCustomNoInputUseYn']['n'];?> /> 사용안함
                                </label>
                            </td>
                        </tr>
                    </table>
                    <p class="notice-info">
                        페이코 바로구매 사용 시 바로구매 주문서에서 고객의 개인통관 고유번호를 <span class="payco_deisignLink">필수</span> 항목으로 입력 받습니다.
                    </p>
                    <p class="notice-info">
                        페이코 바로구매 주문서에 입력된 개인통관 고유번호는 주문/배송 > 주문 관리 > 주문 상세정보 주문서에서 추가 정보로 확인할 수 있습니다.
                    </p>
                    <p class="notice-info">
                        페이코 간편결제 사용 시 고객의 개인통관 고유번호는 <a href="/policy/order_add_field_list.php" target="_blank" class="btn-link">기본설정 > 주문 정책 > 주문서 추가정보 관리</a> 에서 개인통관 고유번호 항목을 추가 등록해야 합니다.
                    </p>
                </div>
            </td>
        </tr>
        <tr>
            <td class="payco_BgColorGray2">페이코 사용 설정</td>
            <td class="payco_BgColorWhite">
                <label class="radio-inline">
                    <input type="radio" name="testYn" value="Y" class="payco_borderZ" <?=$checked['testYn']['Y'];?> /> 테스트하기
                </label> &nbsp;
                <label class="radio-inline">
                    <input type="radio" name="testYn" value="N" class="payco_borderZ" <?=$checked['testYn']['N'];?> /> 실제 사용하기
                </label>
                <p class="notice-info">
                    '테스트하기'를 선택하면 결제버튼이 관리자 로그인 시에만 보여지며, 쇼핑몰에서 결제 시 구매 과정 및 실제 결제는 동일하게 처리됩니다.
                </p>
            </td>
        </tr>
        <tr>
            <td class="payco_BgColorGray2">페이코 결제 설정</td>
            <td class="payco_BgColorWhite">
                <table class="table table-cols payco_textInputLayout mgt10">
                    <tr>
                        <td class="firstTd"><strong>가맹점코드</strong></td>
                        <td>
                            <input type="hidden" name="paycoSellerKey" value="<?= $data['paycoSellerKey'];?>"/>
                            <?php if ($paycoApproval === true) { ?>
                                <span class="text-blue bold"><?php echo $data['paycoSellerKey']; ?></span> <span class="text-blue">(자동 설정 완료)</span>
                            <?php } else { ?>
                                <div class="notice-info notice-danger">페이코 결제 신청이 '승인 완료' 되지 않았습니다. 승인 완료시 자동 노출 됩니다.</div>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="firstTd"><strong>상점ID</strong></td>
                        <td>
                            <input type="hidden" name="paycoCpId" value="<?= $data['paycoCpId'];?>"/>
                            <?php if ($paycoApproval === true) { ?>
                                <span class="text-blue bold"><?php echo $data['paycoCpId']; ?></span> <span class="text-blue">(자동 설정 완료)</span>
                            <?php } else { ?>
                                <div class="notice-info notice-danger">페이코 결제 신청이 '승인 완료' 되지 않았습니다. 승인 완료시 자동 노출 됩니다.</div>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="payco_BgColorGray2">결제수단 설정</td>
            <td class="payco_BgColorWhite">
                <label class="checkbox-inline">
                    <input type="checkbox" name="settleKind[fc]" value="y" <?php echo gd_isset($checked['fc']['y']);?> <?php echo gd_isset($disabled['fc']['y']);?> /> 신용카드
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="settleKind[fb]" value="y" <?php echo gd_isset($checked['fb']['y']);?> <?php echo gd_isset($disabled['fb']['y']);?> /> 바로이체(계좌이체)
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="settleKind[fv]" value="y" <?php echo gd_isset($checked['fv']['y']);?> <?php echo gd_isset($disabled['fv']['y']);?> /> 무통장입금(가상계좌)
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" checked="checked" disabled="disabled" /> 페이코 포인트
                    <input type="hidden" name="settleKind[fp]" value="y" />
                </label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div class="display-inline-block width-sm"><input type="button" onclick="settleKindUpdate();" value="결제수단 새로고침" class="btn btn-gray btn-sm" /></div>
                <p class="notice-info">
                    반드시 페이코와 계약한 결제수단만 체크하세요. 계약한 결제수단을 선택할 수 없다면 [결제수단 새로고침] 버튼을 눌러주세요.
                </p>
            </td>
        </tr>
    </table>

    <div id="part2" class="table-title gd-help-manual">페이코 결제 이용 설정</div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <td class="payco_BgColorGray2">페이코 이용 영역 / 선택</td>
            <td class="payco_BgColorWhite">
                <div>
                    <label class="radio-inline"><input type="radio" name="useYn" value="all" class="payco_borderZ" <?=$checked['useYn']['all'];?> /> PC+모바일 &nbsp;</label>
                    <label class="radio-inline"><input type="radio" name="useYn" value="pc" class="payco_borderZ" <?=$checked['useYn']['pc'];?> /> PC 쇼핑몰 &nbsp;</label>
                    <label class="radio-inline"><input type="radio" name="useYn" value="mobile" class="payco_borderZ" <?=$checked['useYn']['mobile'];?> /> 모바일 쇼핑몰</label>
                </div>
                <p class="notice-info">쇼핑몰에서 페이코 이용 영역을 선택하세요.</p>
            </td>
        </tr>
        <tr>
            <td class="payco_BgColorGray2">페이코 바로구매<br/>버튼 선택</td>
            <td class="payco_BgColorWhite">
                <table width="100%" cellpadding="3" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" name="button_checkout" value="A" class="payco_borderZ" onclick="javascript:changeButtonType('A');" <?=$checked['button_checkout']['A'];?> /> A타입 (277px X 70px)
                                </label> &nbsp;
                                <label class="radio-inline">
                                    <input type="radio" name="button_checkout" value="B" class="payco_borderZ" onclick="javascript:changeButtonType('B');" <?=$checked['button_checkout']['B']; ?> /> B타입 (388px X 84px)
                                </label> &nbsp;
                                <label class="radio-inline">
                                    <input type="radio" name="button_checkout" value="C" class="payco_borderZ" onclick="javascript:changeButtonType('C');" <?=$checked['button_checkout']['C']; ?> /> C타입 (296px X 84px)
                                </label>
                            </div>

                            <div id="buttonTypeA" class="payco_checkoutButton" style="display: none;">
                                <div>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="A1" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['A1']; ?> />
                                        <img src="<?= $image['A1'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="A4" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['A4']; ?> />
                                        <img src="<?= $image['A4'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>

                                <div class="payco_ButtonTypeMargin">
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="A2" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['A2'];?>  />
                                        <img src="<?= $image['A2'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="A5" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['A5'];?>  />
                                        <img src="<?= $image['A5'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>

                                <div class="payco_ButtonTypeMargin">
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="A3" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['A3'];?> />
                                        <img src="<?= $image['A3'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="A6" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['A6'];?> />
                                        <img src="<?= $image['A6'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>
                            </div>

                            <div id="buttonTypeB" class="payco_checkoutButton" style="display: none;">
                                <div>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="B1" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['B1'];?> />
                                        <img src="<?= $image['B1'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="B4" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['B4'];?> />
                                        <img src="<?= $image['B4'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>

                                <div class="payco_ButtonTypeMargin">
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="B2" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['B2'];?> />
                                        <img src="<?= $image['B2'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="B5" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['B5'];?> />
                                        <img src="<?= $image['B5'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>

                                <div class="payco_ButtonTypeMargin">
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="B3" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['B3'];?> />
                                        <img src="<?= $image['B3'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="B6" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['B6'];?> />
                                        <img src="<?= $image['B6'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>
                            </div>

                            <div id="buttonTypeC" class="payco_checkoutButton" style="display: none;">
                                <div>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="C1" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['C1'];?> />
                                        <img src="<?= $image['C1'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="C4" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['C4'];?> />
                                        <img src="<?= $image['C4'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>

                                <div class="payco_ButtonTypeMargin">
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="C2" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['C2'];?> />
                                        <img src="<?= $image['C2'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="C5" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['C5'];?> />
                                        <img src="<?= $image['C5'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>

                                <div class="payco_ButtonTypeMargin">
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="C3" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['C3'];?> />
                                        <img src="<?= $image['C3'];?>" border="0" class="payco_borderZ payco_checkoutDetailSpace"/>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="button_checkoutDetail" value="C6" class="paycoInputRadio1" <?=$checked['button_checkoutDetail']['C6'];?> />
                                        <img src="<?= $image['C6'];?>" border="0" class="payco_borderZ"/>
                                    </label>
                                </div>
                            </div>

                            <p class="notice-info">
                                상품상세 페이지와 장바구니에 노출되는 구매하기 버튼의 타입별 크기와 디자인을 선택합니다.<br/>
                                설정한 스킨에서 바로구매 버튼 이미지가 노출되지 않으면 디자인>디자인페이지수정의 [<a href="<?= URI_ADMIN ?>design/design_page_edit.php?designPageId=goods/goods_view.html" target="_blank"><span class="payco_deisignLink">상품>상품 상세화면</span></a>]
                                및 [<a href="<?= URI_ADMIN ?>design/design_page_edit.php?designPageId=order/cart.html" target="_blank"><span class="payco_deisignLink">주문>장바구니</span></a>]의 "바로구매" 버튼 아래에 치환코드 {payco}를 삽입하여 노출할 수 있습니다.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="payco_BgColorGray2">페이코 간편결제<br/>버튼 선택</td>
            <td class="payco_BgColorWhite">
                <table width="100%" cellpadding="3" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <div class="payco_ButtonTypeMargin">
                                <label class="radio-inline">
                                    <input type="radio" name="button_easypay" value="A1" class="payco_borderZ" <?=$checked['button_easypay']['A1']; ?> />&nbsp;
                                    <img src="<?= $image['easypay_A1'];?>" border="0" class="payco_borderZ"/>
                                </label> &nbsp;&nbsp;&nbsp;
                                <label class="radio-inline">
                                    <input type="radio" name="button_easypay" value="A2" class="payco_borderZ" <?=$checked['button_easypay']['A2']; ?> />&nbsp;
                                    <img src="<?= $image['easypay_A2'];?>" border="0" class="payco_borderZ"/>
                                </label>
                            </div>

                            <p class="notice-info">
                                간편결제 버튼은 상품구매 페이지에서 결제수단을 선택할 때 보여집니다.<br/>
                                설정한 스킨에서 간편결제 버튼 이미지가 노출되지 않으면 디자인>디자인페이지수정의 [<a href="<?= URI_ADMIN ?>design/design_page_edit.php?designPageId=order/order.html" target="_blank"><span class="payco_deisignLink">주문>주문서 작성/결제</span></a>]의 결제수단 안쪽에 치환코드 {payco}를 삽입하여 노출할 수 있습니다.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="payco_BgColorGray2">페이코 이벤트 팝업</td>
            <td class="payco_BgColorWhite">
                <div>
                    <label class="radio-inline"><input type="radio" name="useEventPopupYn" value="y" class="payco_borderZ" <?=$checked['useEventPopupYn']['y'];?> /> 사용함</label>
                    <label class="radio-inline"><input type="radio" name="useEventPopupYn" value="n" class="payco_borderZ" <?=$checked['useEventPopupYn']['n'];?> /> 사용안함</label>
                </div>
                <table class="table table-cols mgt10" id="displayEventPopup">
                    <th class="width-md">PC쇼핑몰 창위치</th>
                    <td class="form-inline">
                        상단:
                        <input type="text" class="form-control input-sm" name="eventPopupTop" value="<?=$data['eventPopupTop']?>"> pixel
                        좌측:
                        <input type="text" class="form-control input-sm" name="eventPopupLeft" value="<?=$data['eventPopupLeft']?>"> pixel
                    </td>
                </table>
                <p class="notice-info">사용함으로 설정 시 주문 페이지에서 페이코 결제시 사용 가능한 쿠폰 혜택이 팝업으로 노출됩니다.</p>
            </td>
        </tr>
    </table>


    <div id="part3" class="table-title gd-help-manual">페이코 결제 예외상품 설정</div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>예외 조건</th>
            <td>
                <span id="presentFlExcept_goods">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="presentExceptFl[]" value="goods" onclick="presentExcept_conf(this.value)" />예외 상품
                    </label>
                </span>
                <span id="presentFlExcept_category">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="presentExceptFl[]" value="category" onclick="presentExcept_conf(this.value)" />예외 카테고리
                    </label>
                </span>
                <span id="presentFlExcept_brand">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="presentExceptFl[]" value="brand" onclick="presentExcept_conf(this.value)" />예외 브랜드
                    </label>
                </span>
            </td>
        </tr>
        <tr id="presentFlExcept_goods_tbl" style="display:none">
            <th>예외 상품
                <div class="mgt10">
                    <button type="button" class="btn btn-gray btn-xs" onclick="layer_register('goods');">상품 선택</button>
                </div>
            </th>
            <td>

                <table id="exceptGoodsTable" class="table table-cols mgt20" style="width:80%">
                    <thead <?php if (is_array($data['exceptGoodsNo']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <th class="width5p">번호</th>
                        <th class="width10p">이미지</th>
                        <th>상품명</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="exceptGoods">
                    <?php
                    if (is_array($data['exceptGoodsNo'])) {
                        foreach ($data['exceptGoodsNo'] as $key => $val) {
                            echo '<tr id="idExceptGoods_' . $val['goodsNo'] . '">' . chr(10);
                            echo '<td>' . ($key + 1) . '<input type="hidden" name="exceptGoods[]" value="' . $val['goodsNo'] . '" /></td>' . chr(10);
                            echo '<td>' . gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank') . '</td>' . chr(10);
                            echo '<td><a href="../goods/goods_register.php?goodsNo=' . $val['goodsNo'] . '" target="_blank">' . $val['goodsNm'] . '</a></td>' . chr(10);
                            echo '<td><button type="button" class="btn btn-gray btn-xs"  onclick="field_remove(\'idExceptGoods_' . $val['goodsNo'] . '\');">삭제</button></td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot <?php if (is_array($data['exceptGoodsNo']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <td colspan="4"><input type="button" value="전체삭제" class="btn btn-default btn-xs btn-gray" onclick="$('#exceptGoods').html('');">
                        </td>
                    </tr>
                    </tfoot>
                </table>

            </td>
        </tr>

        <tr id="presentFlExcept_category_tbl" style="display:none">
            <th>예외 카테고리
                <div class="mgt10">
                    <button type="button" class="btn btn-gray btn-xs" onclick="layer_register('category');">카테고리 선택</button>
                </div>
            </th>
            <td>

                <table id="exceptCategoryTable" class="table table-cols mgt20" style="width:80%">
                    <thead <?php if (is_array($data['exceptCateCd']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <th class="width5p">번호</th>
                        <th>카테고리</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="exceptCategory">
                    <?php
                    if (is_array($data['exceptCateCd'])) {
                        foreach ($data['exceptCateCd']['code'] as $key => $val) {
                            echo '<tr id="idExceptCategory_' . $val . '">' . chr(10);
                            echo '<td>' . ($key + 1) . '<input type="hidden" name="exceptCategory[]" value="' . $val . '" /></td>' . chr(10);
                            echo '<td>' . $data['exceptCateCd']['name'][$key] . '</td>' . chr(10);
                            echo '<td><button type="button" class="btn btn-gray btn-xs"  onclick="field_remove(\'idExceptCategory_' . $val . '\');">삭제</button></td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot <?php if (is_array($data['exceptCateCd']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <td colspan="4">
                            <input type="button" value="전체삭제" class="btn btn-default btn-xs btn-gray" onclick="$('#exceptCategory').html('');"></td>
                    </tr>
                    </tfoot>
                </table>

            </td>

        </tr>

        <tr id="presentFlExcept_brand_tbl" style="display:none">
            <th>예외 브랜드
                <div class="mgt10">
                    <button type="button" class="btn btn-gray btn-xs" onclick="layer_register('brand');">브랜드 선택</button>
                </div>
            </th>
            <td>
                <table id="exceptBrandTable" class="table table-cols mgt20" style="width:80%">
                    <thead <?php if (is_array($data['exceptBrandCd']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <th class="width5p">번호</th>
                        <th>브랜드</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="exceptBrand">
                    <?php
                    if (is_array($data['exceptBrandCd'])) {
                        foreach ($data['exceptBrandCd']['code'] as $key => $val) {
                            echo '<tr id="idExceptBrand_' . $val . '">' . chr(10);
                            echo '<td>' . ($key + 1) . '<input type="hidden" name="exceptBrand[]" value="' . $val . '" /></td>' . chr(10);
                            echo '<td>' . $data['exceptBrandCd']['name'][$key] . '</td>' . chr(10);
                            echo '<td><button type="button" class="btn btn-gray btn-xs"  onclick="field_remove(\'idExceptBrand_' . $val . '\');">삭제</button>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot <?php if (is_array($data['exceptBrandCd']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <td colspan="4"><input type="button" value="전체삭제" class="btn btn-default btn-xs btn-gray" onclick="$('#exceptBrand').html('');">
                        </td>
                    </tr>
                    </tfoot>
                </table>

            </td>

        </tr>

    </table>
</form>

<script type="text/javascript">
    function presentExcept_conf(thisValue) {

        if ($('#presentFlExcept_' + thisValue + "_tbl").is(':hidden')) $('#presentFlExcept_' + thisValue + "_tbl").show();
        else  $('#presentFlExcept_' + thisValue + "_tbl").hide();

    }

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string typeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(typeStr) {
        var layerFormID = 'addPresentForm';

        typeStrId = typeStr.substr(0, 1).toUpperCase() + typeStr.substr(1);

        var parentFormID = 'except' + typeStrId;
        var dataFormID = 'idExcept' + typeStrId;
        var dataInputNm = 'except' + typeStrId;
        var layerTitle = '페이코 예외 조건 - ';

        // 레이어 창
        if (typeStr == 'goods') {
            var layerTitle = layerTitle + '상품';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }
        if (typeStr == 'category') {
            var layerTitle = layerTitle + '카테고리';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }
        if (typeStr == 'brand') {
            var layerTitle = layerTitle + '브랜드';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle
        };

        layer_add_info(typeStr, addParam);

    }

    /**
     * 결제수단 자동 설정 - PG 중앙화에 따른
     *
     */
    function settleKindUpdate() {
        var pgId = '<?php echo $data['paycoCpId'];?>';
        if (pgId == '') {
            alert('페이코 결제 신청을 먼저 진행 하시기 바랍니다.');
        } else {
            var params = {
                mode: 'pgAutoUpdate',
                pgType: 'payco',
                pgId: pgId
            };

            $.post('./settle_ps.php', params, function (data) {
                var resultVal = true;
                var resultMsg = '';
                if (data == '') {
                    resultVal = false;
                    resultMsg = '결과 없음';
                }
                if (resultVal == true) {
                    var resultData = $.parseJSON(data);
                    if (resultData.result == 'ok') {
                        alert('<?php echo $pgNm;?> 정보 갱신 완료 되었습니다. 잠시후 새로고침 됩니다.');
                        setTimeout(function() {
                            parent.location.reload();
                        }, 2000);
                    } else {
                        resultVal = false;
                        resultMsg = resultData.error_msg;
                    }
                }

                if (resultVal == false) {
                    alert('<?php echo $pgNm;?> 정보 갱신에 실패하였습니다. \n서비스 신청이 완료된 상태라면 고객센터로 문의하여 주세요. \n(' + resultMsg + ') ');
                }
            });
        }
    }

    $(document).ready(function () {
        $('#frmPayco').validate({
            submitHandler: function (form) {
                <?php if ($paycoApproval === true) { ?>
                form.target = 'ifrmProcess';
                form.submit();
                <?php } else { ?>
                alert('\'고도 > 쇼핑몰 > 부가서비스 > 페이코 결제\'에서 신규 신청을 하시거나,<br/>신청 승인 상태를 확인하시기 바랍니다.<br/>페이코 결제 설정에 실패 하였습니다.');
                return false;
                <?php } ?>
            }
        });

        <?php if ($paycoApproval === false) { ?>$('input').prop('disabled', true);<?php } ?>

        changeButtonType("<?=$data['button_checkout']?>");

        <?php if (is_array($data['exceptGoodsNo'])) { ?> $('input[name="presentExceptFl[]"][value=goods]').click();<?php  } ?>
        <?php if (is_array($data['exceptCateCd'])) { ?> $('input[name="presentExceptFl[]"][value=category]').click();<?php  } ?>
        <?php if (is_array($data['exceptBrandCd'])) { ?> $('input[name="presentExceptFl[]"][value=brand]').click();<?php  } ?>

        var $use_event_popup_yn = $('input[name="useEventPopupYn"]');
        $use_event_popup_yn.change(function(e) {
            if ($(this).val() === 'y') {
                $('#displayEventPopup').show();
            } else {
                $('#displayEventPopup').hide();
            }
        });
        $use_event_popup_yn.filter(':checked').trigger('click');

        $('input[name="useType"]').click(function(){
            switch(this.value) {
                case 'CE':
                    $('#individualCustomNo').removeClass('display-none');
                    break;
                default:
                    $('#individualCustomNo').addClass('display-none');
                    break;
            }
        });
        $('input[name="useType"]').filter(':checked').trigger('click');
    });
    //-->
</script>
