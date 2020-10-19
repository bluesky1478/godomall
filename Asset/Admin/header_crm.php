<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 * CRM 팝업 상단
 */
$memberFl = $memberData['memberFl'] == 'personal' ? '개인' : '사업자';
$sexFl = $memberData['sexFl'] == 'm' ? '남자' : $memberData['sexFl'] == 's' ? '여자' : '';
$groupInfo = gd_member_groups();
$groupName = $groupInfo[$memberData['groupSno']];
$navTabs = Request::get()->get('navTabs', substr(Request::getInfoUri()['filename'], 11));
$navSubTabs = Request::get()->get('navSubTabs', substr(Request::getInfoUri()['filename'], 11));
$active['navTabs'][$navTabs] = 'active';
$active['navSubTabs'][$navSubTabs] = 'active';
$cellPhone = is_array($memberData['cellPhone']) ? implode('', $memberData['cellPhone']) : $memberData['cellPhone'];
$memberMall = $gGlobal['mallList'][$memberData['mallSno']];
?>
<div class="page-header form-inline crm">
    <h3>회원 관리</h3>
    <form id="formMemberSearch">
        <input type="hidden" name="memNo" value="<?= $memberData['memNo'] ?>">
        <input type="hidden" name="memNm" value="<?= $memberData['memNm'] ?>">
        <input type="hidden" name="cellPhone" value="<?= $cellPhone ?>">
        <div class="pull-right pdr0">
            <label class="control-label"><?= gd_select_box('key', 'key', $combineSearchSelect, null, gd_isset($search['key']), null, null, 'form-control'); ?></label>
            <input type="text" class="form-control" placeholder="회원검색" name="keyword">
            <input type="submit" class="btn btn-hfix btn-black" value="검색">
            <input type="button" class="btn close" value="x">
        </div>
    </form>
</div>

<div class="crm-summary row">
    <div class="col-xs-6 crm-summary-left">
        <h3><a href="member_crm.php?memNo=<?= $memberData['memNo']; ?>"><?= $memberData['memNm'] . '(' . $memberData['nickNm'] . ')' ?></a></h3>
        <button type="button" class="btn btn-icon-mail" id="btnSendMail" data-email="<?= $memberData['email'] ?>" data-mailling-fl="<?= $memberData['maillingFl'] ?>">메일</button>
        <?php if ($memberData['mallSno'] == $gGlobal['defaultMallSno']) { ?>
            <button type="button" class="btn btn-icon-sms js-sms-send" data-cellphone="<?= $cellPhone ?>" data-smsfl="<?= $memberData['smsFl'] ?>" data-memno="<?= $memberData['memNo']; ?>" data-type="select" data-opener="member" data-target-selector="this">SMS
            </button>
        <?php } ?>
        <button type="button" class="btn btn-icon-cs" id="btnCounsel">상담</button>
        <button type="button" class="btn btn-gray">
            <span class="flag flag-16 flag-<?= $memberMall['domainFl']; ?>"></span><?= $memberMall['mallName']; ?>
        </button>
        <ul class="list-inline">
            <li><?= $groupName ?></li>
            <li>최종로그인일: <?= gd_date_format('Y-m-d', $memberData['lastLoginDt']) ?></li>
        </ul>
    </div>
    <div class="col-xs-6 crm-summary-right text-right">
        <ul class="list-inline">
            <li>
                <strong class="icon-mileage">마일리지</strong>
                <div><a href="./member_crm_mileage.php?memNo=<?= $memberData['memNo'] ?>&navTabs=mileage"><?= gd_money_format($memberData['mileage']) . gd_display_mileage_unit() ?></a></div>
            </li>
            <li>
                <strong class="icon-deposit">예치금</strong>
                <div><a href="./member_crm_deposit.php?memNo=<?= $memberData['memNo'] ?>&navTabs=deposit"><?= gd_money_format($memberData['deposit']) . gd_display_deposit('unit') ?></a></div>
            </li>
            <li>
                <strong class="icon-coupon">쿠폰</strong>
                <div><a href="./member_crm_coupon.php?memNo=<?= $memberData['memNo']; ?>&navTabs=coupon"><?= number_format($memberCouponCount); ?></a></div>
            </li>
        </ul>
    </div>
</div>

<ul class="crm-summary-count list-inline">
    <li>총 주문금액
        <strong class="text-red"><?= gd_currency_display($memberData['saleAmt']) ?></strong>
        &nbsp;|
    </li>
    <li>총 상품주문건수 <?= $memberData['saleCnt'] ?>&nbsp;|</li>
    <li>총 상담건수 <?= $memberData['counselCount'] ?>&nbsp;|</li>
    <li>1:1 문의건수
        <strong class="text-red"><?= $memberData['noAnswerCount'] ?></strong>
        /<?= $memberData['questionCount'] ?>
    </li>
</ul>

<ul class="nav nav-tabs mgb20" id="navTabsHeader">
    <li role="presentation" class="<?= $active['navTabs']['0']; ?>">
        <a href="member_crm.php?memNo=<?= $memberData['memNo']; ?>">요약보기</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['detail']; ?>">
        <a href="member_crm_detail.php?memNo=<?= $memberData['memNo']; ?>&navTabs=detail">회원정보</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['order']; ?>">
        <a href="member_crm_order.php?memNo=<?= $memberData['memNo']; ?>&navTabs=order">주문내역</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['mileage']; ?>">
        <a href="member_crm_mileage.php?memNo=<?= $memberData['memNo']; ?>&navTabs=mileage">마일리지내역</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['deposit']; ?>">
        <a href="member_crm_deposit.php?memNo=<?= $memberData['memNo']; ?>&navTabs=deposit">예치금내역</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['coupon']; ?>">
        <a href="member_crm_coupon.php?memNo=<?= $memberData['memNo']; ?>&navTabs=coupon">쿠폰내역</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['cart']; ?>">
        <a href="member_crm_cart.php?memNo=<?= $memberData['memNo']; ?>&navTabs=cart&navSubTabs=cart">장바구니/관심상품</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['sms']; ?>">
        <a href="member_crm_sms.php?memNo=<?= $memberData['memNo']; ?>&navTabs=sms">SMS/알림톡 발송내역</a></li>
    <li role="presentation" class="<?= $active['navTabs']['mail']; ?>">
        <a href="member_crm_mail.php?memNo=<?= $memberData['memNo']; ?>&navTabs=mail">메일발송내역</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['counsel']; ?>">
        <a href="member_crm_counsel.php?memNo=<?= $memberData['memNo']; ?>&navTabs=counsel">상담내역</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['qa']; ?>">
        <a href="member_crm_qa.php?memNo=<?= $memberData['memNo']; ?>&navTabs=qa">문의내역</a>
    </li>
    <li role="presentation" class="<?= $active['navTabs']['board']; ?>">
        <a href="member_crm_board.php?memNo=<?= $memberData['memNo']; ?>&navTabs=board&bdId=goodsreview">게시글내역</a>
    </li>
    <?php if (gd_is_plus_shop(PLUSSHOP_CODE_REVIEW) === true) { ?>
    <li role="presentation" class="<?= $active['navTabs']['plus_review']; ?>">
        <a href="member_crm_plus_review.php?memNo=<?= $memberData['memNo']; ?>&navTabs=plus_review">플러스리뷰</a>
    </li>
    <?php }?>
    <?php if ($cremaUseFl) { ?>
        <li role="presentation" class="<?= $active['navTabs']['crema']; ?>">
            <a href="https://admin.cre.ma/reviews?brand_id=<?= $cremabrandId; ?>&query=<?= $memberData['memId']; ?>" target="_blank">크리마리뷰</a>
        </li>
    <?php }?>
</ul>


<div class="page-header js-affix">
    <?php
    if (empty($navTabs) === false) {
        switch ($navTabs) {
            case 'detail' :
                echo '<h3>회원정보</h3><input type="submit" value="저장" class="btn btn-red btn-register">';
                break;
            case 'order' :
                echo '<h3>주문내역</h3>';
                break;
            case 'mileage' :
                if ($memberData['mallSno'] == $gGlobal['defaultMallSno']) {
                    echo '<h3>마일리지내역</h3><input type="submit" value="마일리지 지급/차감" class="btn btn-red btn-register">';
                }
                break;
            case 'deposit' :
                if ($memberData['mallSno'] == $gGlobal['defaultMallSno']) {
                    echo '<h3>예치금내역</h3><input type="submit" value="예치금 지급/차감" class="btn btn-red btn-register">';
                }
                break;
            case 'coupon' :
                if ($memberData['mallSno'] == $gGlobal['defaultMallSno']) {
                    echo '<h3>쿠폰내역</h3>';
                }
                break;
            case 'cart' :
                echo '<ul class="nav nav-tabs mgt10 mgb10" id="navTabsSubHeader">'.PHP_EOL;
                echo '<li role="presentation" class="'.$active['navSubTabs']['cart'].'">'.PHP_EOL;
                echo '<a href="member_crm_cart.php?memNo='.$memberData['memNo'].'&navTabs=cart&navSubTabs=cart">장바구니(<span id="cartCnt"></span> 건)</a>'.PHP_EOL;
                echo '</li>'.PHP_EOL;
                echo '<li role="presentation" class="'.$active['navSubTabs']['wish'].'">'.PHP_EOL;
                echo '<a href="member_crm_cart.php?memNo='.$memberData['memNo'].'&navTabs=cart&navSubTabs=wish">관심상품(<span id="wishCnt"></span> 건)</a>'.PHP_EOL;
                echo '</li>'.PHP_EOL;
                echo '</ul>';
                break;
            case 'sms' :
                if ($memberData['mallSno'] == $gGlobal['defaultMallSno']) {
                    echo '<h3>SMS/알림톡 발송내역</h3><input type="submit" value="SMS발송" class="btn btn-red btn-register">';
                }
                break;
            case 'mail' :
                echo '<h3>메일발송내역</h3><input type="submit" value="메일발송" class="btn btn-red btn-register">';
                break;
            case 'counsel' :
                echo '<h3>상담내역</h3><input type="submit" value="상담등록" class="btn btn-red btn-register">';
                break;
            case 'qa' :
                echo '<h3>문의내역</h3>';
                break;
            case 'board' :
                echo '<h3>게시글내역</h3>';
                break;
            case 'plus_review' :
                echo '<h3>플러스리뷰</h3>';
                break;
            default:
                break;
        }
    } else {
        echo '<h3>요약보기<small style="margin-left:10px;font-size:12px;">최근 3개월 내 생성된 3건의 내역을 확인할 수 있습니다.</small></h3>';
    }
    ?>
</div>
<style>
    body { overflow-y:scroll; }
</style>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 페이지 접속 권한 없을 때 페이지 헤터 숨기기
        if ($('div.blackout').length > 0) {
            $('#container-wrap #header').css( "background-color", "#ffffff" );
            $('#container-wrap #header').css( "padding", "0px 25px 10px" );
            $('#container-wrap #header .crm-summary').remove();
            $('#container-wrap #header .crm-summary-count').remove();
            $('#container-wrap #header #navTabsHeader').remove();
            $('#container-wrap #header .page-header.js-affix').remove();
            $('#container-wrap #content-wrap #content').css( "margin", "0" );
            $('#container-wrap #footer').remove();
        }
    });
    //-->
</script>
