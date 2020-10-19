 <form id="frmJoinEvent" name="frmJoinEvent" action="../member/member_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="setJoinEventConfig"/>
    <input type="hidden" name="eventType" value="<?=$eventType?>"/>
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?>
            <small></small>
        </h3>
        <div class="btn-group">
            <input type="button" value="이벤트 현황" class="btn btn-white" onclick="location.href='member_join_event_log.php?eventType=<?=$eventType?>'">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title">
        신규회원 가입 혜택 <small>신규 가입회원에게 제공 중인 혜택정보입니다.</small>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>쿠폰</th>
            <td>
                <div style="max-height:280px; overflow-y: auto;">
                    <?php foreach($couponBenefit as $val) { ?>
                        <p>
                            <?=$val['couponNm'].' - ['.$val['couponUseType'].']'.$val['couponKindType'].'('.$val['couponBenefit'].')'.' - '.$val['useEndDate']?>
                            <a href="/promotion/coupon_regist.php?couponNo=<?=$val['couponNo']?>" target="_blank" class="btn btn-sm btn-white">상세보기</a>
                        </p>
                    <?php } ?>
                    <?php if(count($couponBenefit) <= 0) { ?>
                        <p class="mgb0">등록된 ‘회원가입 축하 쿠폰’이 없습니다. <a href="/promotion/coupon_regist.php" target="_blank" class="btn-link">신규쿠폰 등록></a></p>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>마일리지</th>
            <td>
                <?php if($mileageBenefit['payUsableFl'] == 'y') { ?>
                    <?php if($mileageBenefit['giveFl'] == 'y') { ?>
                    <p>신규회원 가입 시 <?=$mileageBenefit['joinAmount']?>원 지급</p>
                    <p>이메일 수신동의 시 <?=($mileageBenefit['emailFl'] == 'y') ? $mileageBenefit['emailAmount']. $mileageBenefit['unit']. ' 추가 지급' : '미지급'?></p>
                    <p>SMS 수신동의 시 <?=($mileageBenefit['smsFl'] == 'y') ? $mileageBenefit['smsAmount']. $mileageBenefit['unit']. ' 추가 지급' : '미지급'?></p>
                    <a href="/member/member_mileage_give.php" target="_blank" class="btn-link">마일리지 지급 설정></a>
                    <?php } else { ?>
                        <p class="mgb0">마일리지 지급설정이 ‘사용안함’으로 설정되어 있습니다. <a href="/member/member_mileage_give.php" target="_blank" class="btn-link">마일리지 지급 설정></a></p>
                    <?php } ?>
                <?php } else { ?>
                    <p class="mgb0">마일리지 사용설정이 ‘사용안함’으로 설정되어 있습니다. <a href="/member/member_mileage_basic.php" target="_blank" class="btn-link">마일리지 기본 설정></a></p>
                <?php } ?>
            </td>
        </tr>
    </table>
    <ul class="nav nav-tabs mgb20">
        <li role="presentation" class="<?= $eventType == "order" ? "active" : "" ?>">
            <a href="/member/member_join_event_config.php?eventType=order" aria-controls="order">주문 간단 가입</a></li>
        <li role="presentation" class="<?= $eventType == "push" ? "active" : "" ?>">
            <a href="/member/member_join_event_config.php?eventType=push" aria-controls="push">회원가입 유도 푸시</a></li>
    </ul>

    <div role="tabpanel" class="tab-pane" id="order">
        <div class="design-notice-box mgb10">
            <strong>주문 간단 가입이란?</strong><br>
            비회원 주문 시 이메일, 비밀번호 입력만으로 회원가입이 가능하고, 주문 시 주문정보가 회원정보에 바로 반영되는 기능입니다.<br>
            비회원 주문 고객에게 신규가입 혜택을 알리고, 즉시 사용 가능한 혜택을 제공하여 회원전환율을 높여보세요!
        </div>

        <div class="table-title">
            주문 간단 가입 기본설정
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>사용 설정</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="useFl" value="y" <?php echo $checked['useFl']['y']; ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="useFl" value="n" <?php echo $checked['useFl']['n']; ?> >
                            사용안함
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>진행범위</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="deviceType" value="all" <?php echo $checked['deviceType']['all']; ?> />
                            PC+모바일
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="deviceType" value="pc" <?php echo $checked['deviceType']['pc']; ?> >
                            PC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="deviceType" value="mobile" <?php echo $checked['deviceType']['mobile']; ?> >
                            모바일
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>쿠폰 혜택</th>
                <td class="form-inline">
                    <select class="form-control width-lg" id="couponNo" name="couponNo" aria-required="true">
                        <option value="">쿠폰 선택</option>
                        <?php
                        foreach($couponData as $val) {
                            $couponSelected = ($data['couponNo'] == $val['couponNo']) ? 'selected="selected"' : '';
                            $couponDisabled = ($val['couponType'] == 'f') ? 'disabled' : '';
                            if ($val['couponType'] != 'f' || $data['couponNo'] == $val['couponNo']) {
                                ?>
                                <option data-couponType="<?=$val['couponType']?>" value="<?= $val['couponNo'] ?>" <?=$couponSelected?> <?=$couponDisabled?>><?= $couponDisabled ? '(발급종료)':''; ?><?= $val['couponNm'] ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <button type="button" class="btn btn-sm btn-gray js-coupon-register" id="btnCouponRegister">신규쿠폰 등록</button>
                    <?php if($data['couponNo']) {?>
                        <a href="/promotion/coupon_regist.php?couponNo=<?=$data['couponNo']?>" target="_blank">선택 쿠폰 상세보기 ></a>
                    <?php } ?>

                    <p class="notice-info">선택가능 쿠폰: 발급상태가 ‘발급중/일시정지’이며, 발급방식이 ‘자동발급‘인 주문 간단 가입 쿠폰<br>
                        사용범위는 ‘PC+모바일’ / 제한조건은 최소로 설정된 쿠폰 사용을 권장드립니다.
                    </p>
                    <p class="notice-info">쿠폰 혜택 설정 시, ‘주문 간단 가입’을 통해 가입한 회원에게 신규회원 가입 혜택 외에 선택된 쿠폰이 추가로 발급됩니다</p>
                </td>
            </tr>
        </table>

        <div class="table-title">
            이벤트 배너 설정
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>사용 설정</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="bannerFl" value="y" <?php echo $checked['bannerFl']['y']; ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="bannerFl" value="n" <?php echo $checked['bannerFl']['n']; ?> >
                            사용안함
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>배너 설정</th>
                <td>
                    <div class="form-inline mgb10">
                        <label class="radio-inline">
                            <input type="radio" name="bannerImageType" value="basic" <?php echo $checked['bannerImageType']['basic']; ?>/>
                            기본 배너
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="bannerImageType" value="self" <?php echo $checked['bannerImageType']['self']; ?>/>
                            이미지 직접 등록
                        </label>
                    </div>
                    <div class="bannerImageType_basic">
                        <img src="<?=PATH_ADMIN_GD_SHARE?>/img/img_sj_default.png" style="width:500px;" />
                    </div>
                    <div class="bannerImageType_self">
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width-sm"/>
                                <col>
                            </colgroup>
                            <tr>
                                <th>PC 쇼핑몰</th>
                                <td>
                                    <input type="hidden" name="bannerImagePc" value="<?=$data['bannerImagePc']?>" />
                                    <input type="file" name="bannerImagePcFile" class="form-control"/>
                                    <?php if ($data['bannerImagePc']) { ?>
                                        <img src="<?=$bannerImagePath.'/'.$data['bannerImagePc']?>" alt="PC이미지"/>
                                    <label class="checkbox-inline"><input type="checkbox" name="bannerImagePcDel" value="Y" /> 삭제</label>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <th>모바일 쇼핑몰</th>
                                <td>
                                    <input type="hidden" name="bannerImageMobile" value="<?=$data['bannerImageMobile']?>" />
                                    <input type="file" name="bannerImageMobileFile" class="form-control"/>
                                    <?php if ($data['bannerImageMobile']) { ?>
                                        <img src="<?=$bannerImagePath.'/'.$data['bannerImageMobile']?>" alt="모바일이미지"/>
                                        <label class="checkbox-inline"><input type="checkbox" name="bannerImageMobileDel" value="Y" /> 삭제</label>
                                    <?php } ?>
                                </td>
                            </tr>
                        </table>
                        <p class="notice-info">jpg, jpeg, png, gif만 등록 가능합니다. 등록된 이미지는 가로사이즈 500px 또는 600px (PC), 100%(모바일) 맞춰 리사이즈되어 노출됩니다.</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div role="tabpanel" class="tab-pane" id="push">
        <div class="design-notice-box mgb10">
            <strong>회원가입 유도푸시란?</strong><br>
            회원가입을 하지 않은 고객에게 신규가입 시 제공되는 혜택정보를 노출하여  회원전환율을 높일 수 있는 알림 서비스입니다.
        </div>

        <div class="table-title">
            회원가입 유도 푸시 기본설정
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>사용 설정</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="pushFl" value="y" <?php echo $checked['pushFl']['y']; ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="pushFl" value="n" <?php echo $checked['pushFl']['n']; ?> >
                            사용안함
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">노출 페이지</th>
                <td>
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-sm"/>
                            <col>
                        </colgroup>
                        <tr>
                            <th>적용범위</th>
                            <td>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applySameFl" value="y" <?=$checked['applySameFl']['y']?> />모바일쇼핑몰 동일적용
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>PC쇼핑몰 페이지</th>
                            <td>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="main" <?=$checked['applyPc']['main']?> />메인
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="goodsView" <?=$checked['applyPc']['goodsView']?> />상품상세정보
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="goodsList" <?=$checked['applyPc']['goodsList']?> />상품리스트
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="search" <?=$checked['applyPc']['search']?> />검색페이지
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="cart" <?=$checked['applyPc']['cart']?> />장바구니
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="orderWrite" <?=$checked['applyPc']['orderWrite']?> />주문서작성
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="boardList" <?=$checked['applyPc']['boardList']?> />게시판 리스트
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="login" <?=$checked['applyPc']['login']?> />로그인
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyPc[]" value="searchOrder" <?=$checked['applyPc']['searchOrder']?> />비회원 주문조회
                                </label>
                            </td>
                        </tr>
                        <tr class="applyMobile">
                            <th>모바일 쇼핑몰</th>
                            <td>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="main" <?=$checked['applyMobile']['main']?> />메인
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="goodsView" <?=$checked['applyMobile']['goodsView']?> />상품상세정보
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="goodsList" <?=$checked['applyMobile']['goodsList']?> />상품리스트
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="search" <?=$checked['applyMobile']['search']?> />검색페이지
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="cart" <?=$checked['applyMobile']['cart']?> />장바구니
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="orderWrite" <?=$checked['applyMobile']['orderWrite']?> />주문서작성
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="boardList" <?=$checked['applyMobile']['boardList']?> />게시판 리스트
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="login" <?=$checked['applyMobile']['login']?> />로그인
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="applyMobile[]" value="searchOrder" <?=$checked['applyMobile']['searchOrder']?> />비회원 주문조회
                                </label>
                            </td>
                        </tr>
                    </table>
                    <p class="notice-info">상품리스트에는 카테고리 / 브랜드 / 기획전 / 타임세일 / 인기상품 / 메인분류 상품리스트 페이지가 적용됩니다.</p>
                </td>
            </tr>
            <tr>
                <th>노출시점</th>
                <td>
                    <div class="radio form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="pushType" value="all" <?=$checked['pushType']['all']?> />페이지 접근 시 마다 푸시 노출
                        </label>
                        <p class="notice-info">설정한 노출페이지에 접근 시 마다 푸시가 노출됩니다.</p>
                    </div>
                    <div class="radio form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="pushType" value="cnt" <?=$checked['pushType']['cnt']?> />쇼핑몰 최초 접근 후
                            <?=gd_select_box('pushCnt', 'pushCnt', gd_array_change_key_value([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]), '', $data['pushCnt'], null);?>
                            번 이상 이동 시 푸시 노출
                        </label>
                        <p class="notice-info">설정한 횟수 만큼 이동 시 설정한 노출페이지에 푸시가 노출됩니다. (방문자 당 1회)</p>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    노출위치
                </th>
                <td>
                    <div  class="pd10" style="float:left">
                        <img src="<?=PATH_ADMIN_GD_SHARE?>img/bandwagon_right.png"><br/>
                        <label class="radio-inline"><input type="radio" name="position" value="right" <?=gd_isset($checked['position']['right']);?> />우측</label>
                    </div>
                    <div  class="pd10" style="float:left">
                        <img src="<?=PATH_ADMIN_GD_SHARE?>img/bandwagon_left.png"><br/>
                        <label class="radio-inline"><input type="radio" name="position" value="left" <?=gd_isset($checked['position']['left']);?> />좌측</label>
                    </div>
                    <div  class="pd10" style="float:left">
                        <img src="<?=PATH_ADMIN_GD_SHARE?>img/bandwagon_center.png"><br/>
                        <label class="radio-inline"><input type="radio" name="position" value="center" <?=gd_isset($checked['position']['center']);?> />중간</label>
                    </div>
                    <div class="notice-info" style="clear:both">모바일쇼핑몰의 경우 선택된 노출위치와 상관없이 가로 100% 사이즈로 출력됩니다.</div>
                </td>
            </tr>
        </table>

        <div class="table-title">
            푸시 노출 정보 설정
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class="width-lg"/>
                <col class="width-md">
                <col class=""/>
            </colgroup>
            <tr>
                <th>아이콘 설정</th>
                <td colspan="3">
                    <div class="form-inline mgb10">
                        <label class="radio-inline">
                            <input type="radio" name="iconType" value="false" <?php echo $checked['iconType']['false']; ?>/>
                            사용안함
                        </label>
                    </div>
                    <div class="form-inline mgb10">
                        <label class="radio-inline">
                            <input type="radio" name="iconType" value="basic" <?php echo $checked['iconType']['basic']; ?>/>
                            기본 아이콘
                        </label>
                        <img src="<?=PATH_ADMIN_GD_SHARE?>/img/icon_default_push.png" />
                    </div>
                    <div class="form-inline mgb10">
                        <label class="radio-inline">
                            <input type="radio" name="iconType" value="self" <?php echo $checked['iconType']['self']; ?>/>
                            직접 등록
                        </label>
                        <input type="hidden" name="pushIcon" value="<?=$data['pushIcon']?>" />
                        <input type="file" name="pushIconFile" class="form-control"/>
                        <?php if ($data['pushIcon']) { ?>
                            <img src="<?=$bannerImagePath.'/'.$data['pushIcon']?>" alt="push icon"/>
                            <label class="checkbox-inline"><input type="checkbox" name="pushIconDel" value="Y" /> 삭제</label>
                        <?php } ?>
                        <p class="notice-info">jpg, jpeg, png, gif만 등록 가능합니다. 등록된 이미지는 가로 50 pixel 맞춰 리사이즈되어 노출됩니다.<br>
                            기본 아이콘 이미지는 50x50 pixel입니다.
                        </p>
                    </div>
                </td>
            </tr>
            <tr>
                <th>푸시 내용 설정</th>
                <td colspan="3">
                    <div class="form-inline mgb10">
                        <label class="radio-inline display-block mgb5">
                            <input type="radio" name="pushDescriptionType" value="text" <?=$checked['pushDescriptionType']['text']?> />가입 유도 문구 입력
                        </label>
                        <textarea name="pushDescriptionText" maxlength="200" rows="5" class="form-control width100p js-maxlength" placeholder="지금 회원가입 시 혜택 제공!"><?=$data['pushDescriptionText']?></textarea>
                    </div>
                        <label class="radio-inline">
                        <input type="radio" name="pushDescriptionType" value="image" <?=$checked['pushDescriptionType']['image']?> />이미지 등록
                        <span class="notice-info">jpg, jpeg, png, gif만 등록 가능합니다. 등록된 이미지는 가로사이즈 420 pixel(PC), 100%(모바일) 맞춰 리사이즈되어 노출됩니다.</span>
                    </label>
                    <table class="table table-cols mgt5">
                        <colgroup>
                            <col class="width-sm"/>
                            <col>
                        </colgroup>
                        <tr>
                            <th>PC 쇼핑몰</th>
                            <td>
                                <input type="hidden" name="pushDescriptionImagePc" value="<?=$data['pushDescriptionImagePc']?>" />
                                <input type="file" name="pushDescriptionImagePcFile" class="form-control"/>
                                <?php if ($data['pushDescriptionImagePc']) { ?>
                                    <img src="<?=$bannerImagePath.'/'.$data['pushDescriptionImagePc']?>" alt="PC이미지"/>
                                    <label class="checkbox-inline"><input type="checkbox" name="pushDescriptionImagePcDel" value="Y" /> 삭제</label>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>모바일 쇼핑몰</th>
                            <td>
                                <input type="hidden" name="pushDescriptionImageMobile" value="<?=$data['pushDescriptionImageMobile']?>" />
                                <input type="file" name="pushDescriptionImageMobileFile" class="form-control"/>
                                <?php if ($data['pushDescriptionImageMobile']) { ?>
                                    <img src="<?=$bannerImagePath.'/'.$data['pushDescriptionImageMobile']?>" alt="모바일이미지"/>
                                    <label class="checkbox-inline"><input type="checkbox" name="pushDescriptionImageMobileDel" value="Y" /> 삭제</label>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="pushDescriptionType">
                <th>배경 색상 선택</th>
                <td>
                    <label class="radio-inline">
                        <input type="text" name="bgColor" value="<?php echo $data['bgColor']; ?>" readonly maxlength="7" class="form-control width-xs center color-selector" />
                    </label>
                </td>
                <th>텍스트 색상 선택</th>
                <td>
                    <label class="radio-inline">
                        <input type="text" name="textColor" value="<?php echo $data['textColor']; ?>" readonly maxlength="7" class="form-control width-xs center color-selector" />
                    </label>
                </td>
            </tr>
        </table>
    </div>
</form>

<script>
    $(document).ready(function(){
        $('.js-coupon-regist').click(function (e) {
            window.open('/promotion/coupon_regist.php');
        });
        $('input[name=bannerImageType]').change(function(){
            display_bannerImageType();
        });
        $('input[name=applySameFl]').change(function(){
            display_applyMobile();
        });
        $('input[name=pushDescriptionType]').change(function(){
            display_pushDescriptionType();
        })
        display_bannerImageType();
        display_applyMobile();
        display_pushDescriptionType();

        var eventType = '<?=$eventType?>';
        $('.tab-pane').hide();
        $('#'+eventType).show();

        $("#frmJoinEvent").validate({
            dialog: false,
            ignore: '.ignore',
            rules: {
            },
            messages: {
            },
            submitHandler: function (form) {
                if((eventType == 'order' && !checkOrder()) || (eventType == 'push' && !checkPush())) {
                    return false;
                }
                form.target = 'ifrmProcess';
                form.submit();
            }
        });
    });
    function checkOrder(){
        var couponType = $("#couponNo option:selected").attr('data-couponType');
        if(couponType == 'f') {
            alert('발급종료 상태의 쿠폰이 선택되었습니다. 쿠폰을 다시 선택해주세요.');
            return false;
        }
        if($(':radio[name="bannerImageType"]:checked').val() == 'self') {
            if((!$('input[name=bannerImagePc]').val() && !$('input[name=bannerImagePcFile]').val()) ||
                (!$('input[name=bannerImageMobile]').val() && !$('input[name=bannerImageMobileFile]').val()) ||
                ($('input[name=bannerImagePcDel]').is(':checked') && !$('input[name=bannerImagePcFile]').val()) ||
                ($('input[name=bannerImageMobileDel]').is(':checked') && !$('input[name=bannerImageMobileFile]').val()) ){
                alert('배너 이미지를 등록해주세요.');
                return false;
            }
        }
        return true;
    }
    function checkPush(){
        var pushFl = $('#frmJoinEvent input:radio[name=pushFl]:checked').val();
        if(pushFl == 'y') {
            var sameFl = $('#frmJoinEvent input:checkbox[name=applySameFl]:checked').length;
            var len = $('#frmJoinEvent input:checkbox[name^=apply]:visible:checked').length;
            if (len - sameFl <= 0) {
                alert('노출페이지를 설정해주세요.');
                return false;
            }
        }
        if($(':radio[name="iconType"]:checked').val() == 'self') {
            if((!$('input[name=pushIcon]').val() && !$('input[name=pushIconFile]').val()) ||
                ($('input[name=pushIconDel]').is(':checked') && !$('input[name=pushIconFile]').val()) ){
                alert('아이콘 이미지를 등록해주세요.');
                return false;
            }
        }
        if($(':radio[name="pushDescriptionType"]:checked').val() == 'image') {
            if((!$('input[name=pushDescriptionImagePc]').val() && !$('input[name=pushDescriptionImagePcFile]').val()) ||
                (!$('input[name=pushDescriptionImageMobile]').val() && !$('input[name=pushDescriptionImageMobileFile]').val()) ||
                ($('input[name=pushDescriptionImagePcDel]').is(':checked') && !$('input[name=pushDescriptionImagePcFile]').val()) ||
                ($('input[name=pushDescriptionImageMobileDel]').is(':checked') && !$('input[name=pushDescriptionImageMobileFile]').val()) ){
                alert('푸시 내용 이미지를 등록해주세요.');
                return false;
            }
        }
        return true;
    }
    function display_bannerImageType(){
        var type = $('input[name=bannerImageType]:checked').val();
        $('div[class^=bannerImageType]').hide();
        $('.bannerImageType_'+type).show();
    }
    function display_applyMobile(){
        if($('input[name=applySameFl]').is(':checked')) {
            $('.applyMobile').hide();
        } else {
            $('.applyMobile').show();
        }
    }
    function display_pushDescriptionType(){
        if($('input[name=pushDescriptionType]:checked').val() == 'text') {
            $('.pushDescriptionType').css('visibility', '');
        } else {
            $('.pushDescriptionType').css('visibility', 'hidden');
        }
    }
</script>