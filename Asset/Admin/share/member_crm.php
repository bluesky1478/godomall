<div class="" id="pageContentWrapper">
    <div class="table-title">회원정보
        <div class="pull-right">
            <a href="member_crm_detail.php?memNo=<?= $memberData['memNo']; ?>&navTabs=detail" class="btn btn-sm btn-link">더보기</a>
        </div>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="width-2xl"/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>회원구분</th>
            <td><?= $memberData['memberFl']; ?></td>
            <th>상점 구분</th>
            <td>
                <span class="flag flag-16 flag-<?= $memberMall['domainFl']; ?>"></span><?= $memberMall['mallName']; ?>
            </td>
        </tr>
        <tr>
            <th>이름</th>
            <td id="memNm">
                <?= $memberData['memNm']; ?>
                <?php if (gd_isset($memberData['pronounceName'], '-') != '-') {
                    echo '(' . $memberData['pronounceName'] . ')';
                } ?>
            </td>
            <th>아이디</th>
            <td id="memId"><?= $memberData['memId']; ?></td>
        </tr>
        <tr>
            <th>회원가입일</th>
            <td><?= $memberData['regDt']; ?></td>
            <th>전화번호</th>
            <td id="phone">
                <span class="mgr5"><?= $memberData['phoneCountryCodeNameKor']; ?></span><?= $memberData['phone']; ?></td>
        </tr>
        <tr>
            <th>휴대폰</th>
            <td id="cellPhone">
                <span class="mgr5"><?= $memberData['cellPhoneCountryCodeNameKor']; ?></span><?= $memberData['cellPhone']; ?></td>
            <th>이메일</th>
            <td id="email"><?= $memberData['email']; ?></td>
        </tr>
        <tr>
            <th>주소</th>
            <td colspan="3"><?= $memberData['zonecode'] . (strlen($memberData['zipcode']) == 7 ? '(' . $memberData['zipcode'] . ')' : '') . ' ' . $memberData['address'] . ' ' . $memberData['addressSub']; ?></td>
        </tr>
        <tr>
            <th>성별</th>
            <td><?php if ($memberData['sexFl'] == 'm') {
                    echo '남자';
                } elseif ($memberData['sexFl'] == 'w') {
                    echo '여자';
                } ?>
            </td>
            <th>나이</th>
            <td><?= gd_age(str_replace('-', '', ($memberData['birthDt'] == '0000-00-00') ? 0 : $memberData['birthDt']), 2); ?></td>
        </tr>
        </tbody>
    </table>
    <div class="table-title">주문내역
        <div class="pull-right">
            <a href="member_crm_order.php?memNo=<?= $memberData['memNo'] ?>&navTabs=order" class="btn btn-sm btn-link">더보기</a>
        </div>
    </div>
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width3p">번호</th>
            <th class="width5p">주문일시</th>
            <th class="width10p">주문번호</th>
            <th>주문상품</th>
            <th class="width7p">총 주문금액</th>
            <th class="width7p">총 실제결제금액</th>
            <th class="width7p">결제상태</th>
            <th class="width3p">미배송</th>
            <th class="width3p">배송중</th>
            <th class="width3p">배송완료</th>
            <th class="width3p">취소</th>
            <th class="width3p">교환</th>
            <th class="width3p">반품</th>
            <th class="width3p">환불</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($orderGoodsData) === false && is_array($orderGoodsData)) {
            $sortNo = 1; // 번호 설정
            $totalCnt = 0; // 주문서 수량 설정
            $totalGoods = 0; // 주문서 수량 설정
            $totalPrice = 0; // 주문 총 금액 설정
            foreach ($orderGoodsData as $orderNo => $orderData) {
                $rowCnt = $orderData['cnt']['goods']['all']; // 한 주문당 상품주문 수량
                $rowChk = 0; // 한 주문당 첫번째 주문 체크용
                $rowAddChk = 0; //
                $totalCnt++; // 주문서 수량
                foreach ($orderData['goods'] as $sKey => $sVal) {
                    $rowScm = 0;
                    foreach ($sVal as $dKey => $dVal) {
                        $rowDelivery = 0;
                        foreach ($dVal as $key => $val) {
                            $goodsPrice = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                            $settlePrice = ($val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'])) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['divisionCouponOrderDcPrice'];

                            $totalGoods++; // 상품 수량
                            if ($key === 0) {
                                $totalPrice = $totalPrice + $val['settlePrice']; // 주문 총 금액(누적)
                            }
                            if (in_array($val['statusMode'], $statusListCombine)) {
                                $checkBoxCd = $orderNo;
                            } else {
                                $checkBoxCd = $orderNo . INT_DIVISION . $val['sno'];
                            }

                            // 주문일괄처리 제외대상 비활성화
                            if ($isUserHandle) {
                                $checkDisabled = ($isUserHandle && $val['userHandleFl'] != 'r' ? 'disabled="disabled"' : '');
                            } else {
                                if (in_array(
                                    $currentStatusCode, [
                                        'b',
                                        'e',
                                        'r',
                                    ]
                                )) {
                                    // 교환/반품/환불완료일 경우 체크 불가
                                    $checkDisabled = (in_array(
                                        $val['orderStatus'], [
                                            'b4',
                                            'e5',
                                            'r3',
                                        ]
                                    ) === false ? '' : 'disabled="disabled"');
                                } else {
                                    $checkDisabled = (!gd_isset($statusExcludeCd, []) || in_array($val['statusMode'], $statusExcludeCd) === false ? '' : 'disabled="disabled"');
                                }
                            }

                            // 테스트용으로 만듬 삭제 할 것
                            $checkDisabled = false;

                            // rowspan 처리
                            $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';
                            $orderAddGoodsRowSpan = $val['addGoodsCnt'] > 0 ? 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"' : '';
                            $orderScmRowSpan = ' rowspan="' . ($orderData['cnt']['scm'][$sKey]) . '"';
                            $orderDeliveryRowSpan = ' rowspan="' . ($orderData['cnt']['delivery'][$dKey]) . '"';
                            ?>
                            <tr class="text-center">
                                <td <?= $orderAddGoodsRowSpan ?> class="font-num">
                                    <small><?= $page->idx--; ?></small>
                                </td>
                                <?php if ($rowChk === 0) { ?>
                                    <td <?= $orderGoodsRowSpan; ?> class="font-date"><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?></td>
                                <?php } ?>
                                <?php if ($rowChk === 0) { ?>
                                    <td <?= $orderGoodsRowSpan; ?>>
                                        <?php if ($val['firstSaleFl'] == 'y') { ?>
                                            <img src="<?= PATH_ADMIN_GD_SHARE ?>img/order/icon_firstsale.png" alt="첫주문"/>
                                        <?php } ?>
                                        <a href="#" title="주문번호" class="btn btn-link font-num" onclick="order_view_popup('<?= $orderNo; ?>');"><?= $orderNo; ?></a>
                                        <?php if ($val['orderChannelFl'] == 'naverpay') { ?>
                                            <p>
                                                <a href="./order_view.php?orderNo=<?= $orderNo; ?>" target="_blank" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>"><img
                                                        src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/> <?= $val['apiOrderNo']; ?></a>
                                            </p>
                                        <?php } else if($val['orderChannelFl'] == 'payco') { ?>
                                            <img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'payco.gif')->www() ?>"/>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                                <td class="text-left">
                                    <div class="goods_name one-line hand" title="주문 상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>');"><?= gd_html_cut($val['orderGoodsNm'], 46, '..'); ?></div>
                                    <?php
                                    // 옵션 처리
                                    if (empty($val['optionInfo']) === false) {
                                        echo '<div class="option_info" title="상품 옵션">';
                                        foreach ($val['optionInfo'] as $option) {
                                            echo $option[0] . ':', $option[1] . ', ';
                                        }
                                        echo '</div>' . chr(10);

                                    }

                                    // 텍스트 옵션 처리
                                    if (empty($val['optionTextInfo']) === false) {
                                        echo '<div class="option_info" title="텍스트 옵션">';
                                        foreach ($val['optionTextInfo'] as $option) {
                                            echo $option[0] . ':', $option[1] . ', ';
                                        }
                                        echo '</div>' . chr(10);
                                    }
                                    ?>
                                </td>
                                <?php if ($rowChk === 0 && !$isUserHandle) { ?>
                                    <td <?= $orderGoodsRowSpan; ?>><?= gd_currency_symbol() ?><?= gd_money_format($val['totalOrderPrice']); ?></span><?= gd_currency_string() ?></td>
                                    <td <?= $orderGoodsRowSpan; ?>><?= gd_currency_symbol() ?><?= gd_money_format($val['settlePrice']); ?></span><?= gd_currency_string() ?></td>
                                <?php } ?>
                                <td <?= $orderAddGoodsRowSpan ?>>
                                    <?php if (in_array(substr($val['orderStatus'], 0, 1), ['o','c'])) { ?>
                                        미결제
                                    <?php } elseif (in_array(substr($val['orderStatus'], 0, 1), ['f'])) { ?>
                                        <?=$val['orderStatusStr']?>
                                    <?php } else { ?>
                                        결제확인
                                    <?php } ?>
                                </td>
                                <td><?=$val['noDelivery'];?></td>
                                <td><?=$val['deliverying'];?></td>
                                <td><?=$val['deliveryed'];?></td>
                                <td><?=$val['cancel'];?></td>
                                <td><?=$val['exchange'];?></td>
                                <td><?=$val['back'];?></td>
                                <td><?=$val['refund'];?></td>
                            </tr>
                            <?php
                            if ($val['addGoodsCnt'] > 0) {
                                foreach ($val['addGoods'] as $aVal) {
                                    ?>
                                    <tr class="text-center add-goods">
                                        <td class="text-left">
                                            <span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span>
                                            <div class="goods_name one-line hand" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo']; ?>');"><?= gd_html_cut($aVal['goodsNm'], 46, '..'); ?>
                                                <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..'); ?>)</small>
                                            </div>
                                        </td>
                                        <td class="goods_cnt"><?= number_format($aVal['goodsCnt']); ?></td>
                                        <!--                                    <td>--><?//= gd_currency_display($aVal['goodsPrice'] * $aVal['goodsCnt']); ?><!--</td>-->
                                    </tr>
                                    <?php
                                    $rowChk++;
                                }
                            } else {
                                $rowChk++;
                            }
                            $rowScm++;
                            $rowDelivery++;
                        }
                    }
                }
            }
        } else {
            ?>
            <tr>
                <td colspan="20" class="no-data">
                    검색된 주문이 없습니다.
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="table-title">상담내역
        <div class="pull-right">
            <a href="member_crm_counsel.php?memNo=<?= $memberData['memNo']; ?>&navTabs=counsel" class="btn btn-sm btn-link">더보기</a>
        </div>
    </div>
    <table class="table table-rows">
        <thead>
        <tr>
            <th>번호</th>
            <th class="width-xs">등록일</th>
            <th>등록자</th>
            <th>상담수단</th>
            <th>상담구분</th>
            <th>상담내용</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $crmListHtml = [];
        $crmIndex = count($memberCrmList);
        $methodArray = array('m'=>'메일', 'p'=>'전화');
        $kindArray = array('o'=>'주문', 'd'=>'배송','c'=>'취소환불','e'=>'오류','etc'=>'기타');

        if ($crmIndex > 0) {
            foreach ($memberCrmList as $key => $value) {
                $crmListHtml[] = '<tr><td class="font-num center">' . $crmIndex-- . '</td><td class="center text-nowrap">' . $value['regDt'] . '</td><td class="center text-nowrap">' . $value['managerId'] . '<br/> (' . $value['managerNm'] . ')</td><td class="center">' . $value['method'] . '</td><td class="center">' . $value['kind'] . '</td><td class="left"  style="width:100% !important;word-break:break-all;" wrap="hard">' . str_replace(
                        [
                            '\r\n',
                            '\r',
                            '\n',
                        ], '<br />', $value['contents']
                    ) . '</td></tr>';
            }
        } else {
            $crmListHtml[] = '<tr><td colspan="6" class="no-data">등록된 상담내역이 없습니다.</td></tr>';
        }
        echo implode('', $crmListHtml);
        ?>
        </tbody>
    </table>
    <div class="table-title">1:1문의내역
        <div class="pull-right">
            <a href="member_crm_qa.php?memNo=<?= $memberData['memNo'] ?>&navTabs=qa" class="btn btn-sm btn-link">더보기</a>
        </div>
    </div>
    <table class="table table-rows">
        <colgroup>
            <col class="width-xs">
            <col>
            <col class="width-md">
            <col>
            <col>
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>제목</th>
            <th>작성일</th>
            <th>조회</th>
            <th>답변여부</th>
            <th>답변</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $qnaListHtml = [];
        $qnaIndex = count($qnaList);
        if ($qnaIndex > 0) {
            foreach ($qnaList as $key => $value) {
                $qnaListHtml[] = '<tr><td class="center">' . $qnaIndex-- . '</td><td>' . $value['subject'] . '</td><td class="center">' . $value['regDt'] . '</td><td class="center">' . $value['hit'] . '</td><td align="center">' . $value['replyStatusText'] . '</td>';
                $qnaListHtml[] = '<td class="center"><a onclick="replyArticle(' . $value['sno'] . ');" class="btn btn-sm btn-white">답변</a>
                </td></tr>';
            }
        } else {
            $qnaListHtml[] = '<tr><td colspan="6" class="no-data">등록된 1:1문의가 없습니다.</td></tr>';
        }
        echo implode('', $qnaListHtml);
        ?>
        </tbody>
    </table>
    <!--/row-->
</div>
<!--/span-->
<script type="text/javascript">
    function replyArticle(sno) {
        window.open("../board/article_write.php?bdId=qa&mode=reply&sno="  + ((sno) ? sno : ""), "1:1문의 게시판", 'width=1600,height=800,scrollbars=yes,resizable=yes');
    }
</script>
