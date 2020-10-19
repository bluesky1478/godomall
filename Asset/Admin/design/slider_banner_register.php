<style>
    .modal {display:block !important;}
    .swiper-container {
        margin: 0 auto;
        position: relative;
        overflow:hidden;
        /* Fix of Webkit flickering */
        z-index: 1;
        width: <?php echo $data['bannerSize']['width'] . $data['bannerSize']['sizeType']; ?>;
        <?php if ($data['bannerSize']['sizeType'] != '%') {?>
        height: <?php echo $data['bannerSize']['height']; ?>px;
        <?php } ?>
    }

    .swiper-wrapper  {
        width: <?php echo $data['bannerSize']['width'] . $data['bannerSize']['sizeType']; ?>;
        <?php if ($data['bannerSize']['sizeType'] != '%') {?>
        height: <?php echo $data['bannerSize']['height']; ?>px;
        <?php } ?>
        overflow:hidden;
    }

    .swiper-wrapper .slick-slide img {
        width: <?php echo $data['bannerSize']['width'] . $data['bannerSize']['sizeType']; ?>;
        <?php if ($data['bannerSize']['sizeType'] != '%') {?>
        height: <?php echo $data['bannerSize']['height']; ?>px;
        <?php } ?>
    }
    .swiper-button-prev, .swiper-button-next {
        font-size: 0;
        line-height: 0;
        position: absolute;
        top: 45%;
        display: block;
        width: 27px;
        height: 44px;
        padding: 0;
        -webkit-transform: translate(0, -45%);
        -ms-transform: translate(0, -45%);
        transform: translate(0, -45%);
        cursor: pointer;
        z-index:10;
        border:0px;
        background:none;
    }

     .swiper-button-prev {
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2027%2044'%3E%3Cpath%20d%3D'M0%2C22L22%2C0l2.1%2C2.1L4.2%2C22l19.9%2C19.9L22%2C44L0%2C22L0%2C22L0%2C22z'%20fill%3D'%23<?php echo str_replace('#', '', $data['sideButton']['activeColor']); ?>'%2F%3E%3C%2Fsvg%3E");
        background-repeat:no-repeat;
        left: 10px;
        right: auto;
    }
    .swiper-button-next {
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2027%2044'%3E%3Cpath%20d%3D'M27%2C22L27%2C22L5%2C44l-2.1-2.1L22.8%2C22L2.9%2C2.1L5%2C0L27%2C22L27%2C22z'%20fill%3D'%23<?php echo str_replace('#', '', $data['sideButton']['activeColor']); ?>'%2F%3E%3C%2Fsvg%3E");
        background-repeat:no-repeat;
        right: 10px;
        left: auto;
    }

    .swiper-wrapper  .slick-dots {
        position: absolute;
        bottom: 10px;
        display: block;
        width: 100%;
        padding: 0;
        margin: 0;
        text-align: center;
    }
    .swiper-wrapper  .slick-dots li {
        position: relative;
        display: inline-block;
        margin: 0 5px;
        padding: 0;
        cursor: pointer;
    }

    .swiper-wrapper  .slick-dots li button {
        font-size: 0;
        line-height: 0;
        display: block;
        width: <?php echo $data['pageButton']['size']; ?>px;
        height: <?php echo $data['pageButton']['size']; ?>px;
        padding: 5px;
        cursor: pointer;
        border: 0;
        outline: none;
        border-radius:<?php echo $data['pageButton']['radius']; ?>%;
        background: <?php echo $data['pageButton']['inactiveColor']; ?>;
        opacity:0.75;
    }

    .swiper-wrapper .slick-dots li.slick-active button {
        background: <?php echo $data['pageButton']['activeColor']; ?>;
        opacity:1;
    }
</style>

<form id="frmBanner" name="frmBanner" method="post" action="./slider_banner_ps.php" target="ifrmProcess" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?php echo $mode; ?>"/>
    <input type="hidden" name="sno" value="<?php echo gd_isset($data['sno']); ?>"/>
    <input type="hidden" name="bannerFolder" value="<?php echo gd_isset($data['bannerFolder']); ?>"/>
    <input type="hidden" name="bannerCode" value="<?php echo gd_isset($data['bannerCode']); ?>"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>움직이는 배너 정보를 <?php echo $modeTxt; ?> 합니다.</small>
        </h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?=$adminList;?>');" />
            <input type="submit" value="<?php echo end($naviMenu->location); ?>" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        기본 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">배너 제목</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="bannerTitle" value="<?php echo $data['bannerTitle']; ?>" maxlength="50" class="form-control js-maxlength width-2xl"/>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">구분</th>
            <td>
                <?php if ($mode === 'modifySliderBanner') {?>
                    <input type="hidden" name="bannerDeviceType" value="<?php echo $data['bannerDeviceType'];?>" />
                    <span class="bold"><?php echo $bannerDeviceType[$data['bannerDeviceType']];?>쇼핑몰</span>
                <?php } else {?>
                    <?php foreach ($bannerDeviceType as $dKey => $dVal) {?>
                        <label class="radio-inline">
                            <input type="radio" name="bannerDeviceType" value="<?php echo $dKey;?>" <?php echo $checked['bannerDeviceType'][$dKey]; ?> /><?php echo $dVal;?>쇼핑몰
                        </label>
                    <?php }?>
                <?php }?>
            </td>
        </tr>
        <tr>
            <th class="require">디자인 스킨</th>
            <td class="form-inline">
                <?php if ($mode === 'modifySliderBanner') {?>
                    <input type="hidden" name="skinName" value="<?php echo $data['skinName'];?>" />
                    <span class="bold"><?php echo $data['selectedSkinName'];?></span>
                <?php } else {?>
                    <select name="skinName" class="form-control width-2xl">
                    </select>
                <?php }?>
            </td>
        </tr>
        <?php if (empty($data['bannerCode']) === false) { ?>
            <tr>
                <th>치환코드</th>
                <td>
                    <div class="form-inline font-num">
                        <?php echo '{=includeWidget(\'proc/_slider_banner.html\', \'bannerCode\', \'' . $data['bannerCode'] . '\')}'; ?>
                        <button type="button" title="치환코드 복사" class="btn btn-white btn-sm js-clipboard"  data-clipboard-text="{=includeWidget('proc/_slider_banner.html', 'bannerCode', '<?php echo $data['bannerCode']; ?>')}">복사하기</button>
                    </div>
                </td>
            </tr>
        <?php }?>
        <tr>
            <th>노출 여부</th>
            <td>
                <label class="radio-inline"><input type="radio" name="bannerUseFl" value="y" <?php echo gd_isset($checked['bannerUseFl']['y']); ?> />노출</label>
                <label class="radio-inline"><input type="radio" name="bannerUseFl" value="n" <?php echo gd_isset($checked['bannerUseFl']['n']); ?> />미노출</label>
            </td>
        </tr>
        <tr>
            <th>노출 기간</th>
            <td>
                <div class="form-inline">
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
        <tr>
            <td colspan="2">
                <div id="preview-area" style="width:820px; max-width:100%; margin:20px auto; text-align:center;">
                    <div id="preview-banner">
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                <?php
                                foreach($data['bannerInfo'] as $value) if ($value['bannerImage']) {
                                    $bannerImg = gd_html_banner_image(($data['bannerImagePath'] . $data['bannerFolder'] . '/' . $value['bannerImage']), $value['bannerImageAlt']);
                                    if (!$bannerImg) $bannerImg = '<img src="' . PATH_ADMIN_GD_SHARE . $value['bannerImage'] . '" />';
                                    ?>
                                    <div class="swiper-slide"><?php echo $bannerImg; ?></div>
                                <?php } ?>
                            </div>

                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>전환 속도 선택</th>
            <td class="form-inline">
                <?php echo gd_select_box(null, 'bannerSliderConf[speed]', ['300' => '빠르게', '1300' => '보통', '3300' => '느리게'], null, $data['bannerSliderConf']['speed'], null); ?>
            </td>
        </tr>
        <tr>
            <th>전환 시간 설정</th>
            <td class="form-inline">
                <?php echo gd_select_box(null, 'bannerSliderConf[time]', $bannerSliderTime, null, $data['bannerSliderConf']['time'], null); ?>
                <span class="notice-info slider-banner-auto">”수동” 선택 시에는 사용자가 마우스로 클릭하는 경우에만 이미지가 전환됩니다.</span>
            </td>
        </tr>
        <tr>
            <th>효과 선택</th>
            <td class="form-inline">
                <label class="radio-inline"><input type="radio" name="bannerSliderConf[effect]" value="slide" <?php echo gd_isset($checked['bannerSliderConf']['effect']['slide']); ?> />슬라이드</label>
                <label class="radio-inline"><input type="radio" name="bannerSliderConf[effect]" value="fade" <?php echo gd_isset($checked['bannerSliderConf']['effect']['fade']); ?> />페이드</label>
            </td>
        </tr>
        <tr>
            <th>좌우 전환 버튼</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="sideButton[useFl]" value="y" <?php echo gd_isset($checked['sideButton']['useFl']['y']); ?> />노출</label>
                    <label class="radio-inline"><input type="radio" name="sideButton[useFl]" value="n" <?php echo gd_isset($checked['sideButton']['useFl']['n']); ?> />미노출</label>
                </div>
                <div id="bannerSideButtonConf" class="form-inline mgt10 mgl10">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-xs"/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th>색상</th>
                            <td>
                                <div class="form-inline">
                                    <label class="radio-inline">
                                        <input type="text" name="sideButton[activeColor]" value="<?php echo $data['sideButton']['activeColor']; ?>"  readonly maxlength="7" class="form-control width-xs center color-selector" />
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <th>네비게이션 설정</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="pageButton[useFl]" value="y" <?php echo gd_isset($checked['pageButton']['useFl']['y']); ?> />노출</label>
                    <label class="radio-inline"><input type="radio" name="pageButton[useFl]" value="n" <?php echo gd_isset($checked['pageButton']['useFl']['n']); ?> />미노출</label>
                    <label class="radio-inline"><input type="radio" name="pageButton[useFl]" value="c" <?php echo gd_isset($checked['pageButton']['useFl']['c']); ?> />직접 등록</label><br />
                    <span class="notice-info nav-info dusplay-none">[직접 등록] 의 경우, 하단 배너 이미지 설정에서 각 이미지별로 네비게이션 버튼을 등록하시면 됩니다.</span>
                </div>
                <div id="bannerPageButtonConf" class="form-inline mgt10 mgl10">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-xs"/>
                            <col class="width-2xl"/>
                            <col class="width-xs"/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th>종류</th>
                            <td colspan="3">
                                <div class="form-inline">
                                    <?php
                                    $buttonRadius = ['100', '20', '0'];
                                    foreach ($buttonRadius as $val) {
                                        echo '<label class="radio-inline">';
                                        echo '<input type="radio" name="pageButton[radius]" value="' . $val . '" '. gd_isset($checked['pageButton']['radius'][$val]) . '  style="margin-top:-5px;"/>';
                                        echo '<div style="display: inline-block; width: 13px; height: 13px; background: #000; border-radius: ' . $val . '%;"></div> ';
                                        echo '<div style="display: inline-block; width: 13px; height: 13px; background: #000; border-radius: ' . $val . '%;"></div> ';
                                        echo '<div style="display: inline-block; width: 13px; height: 13px; background: #000; border-radius: ' . $val . '%;"></div> ';
                                        echo '<div style="display: inline-block; width: 13px; height: 13px; background: #000; border-radius: ' . $val . '%;"></div> ';
                                        echo '</label>';
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>활성 색상</th>
                            <td>
                                <div class="form-inline">
                                    <label class="radio-inline">
                                        <input type="text" name="pageButton[activeColor]" value="<?php echo $data['pageButton']['activeColor']; ?>" readonly maxlength="7" class="form-control width-xs center color-selector" />
                                    </label>
                                </div>
                            </td>
                            <th>비활성 색상</th>
                            <td>
                                <div class="form-inline">
                                    <label class="radio-inline">
                                        <input type="text" name="pageButton[inactiveColor]" value="<?php echo $data['pageButton']['inactiveColor']; ?>" readonly maxlength="7" class="form-control width-xs center color-selector" />
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>크기</th>
                            <td colspan="3">
                                <div class="form-inline">
                                    <?php
                                    $buttonSize = ['8', '10', '12', '16', '20', '26'];
                                    foreach ($buttonSize as $val) {
                                        echo '<label class="radio-inline">';
                                        echo '<input type="radio" name="pageButton[size]" value="' . $val . '" '. gd_isset($checked['pageButton']['size'][$val]) . ' />';
                                        echo '<span class="bold inline-block" style="font-size: ' . $val . 'px;">●●●</span></label>';
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        배너이미지 설정
    </div>
    <table id="bannerImage" class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">배너 사이즈</th>
            <td>
                <div class="form-inline">
                    <label>
                        가로크기 :
                        <input type="text" name="bannerSize[width]" value="<?php echo $data['bannerSize']['width']; ?>" class="form-control js-number width-2xs center" data-number="4, 2000, 700" />
                        <select name="bannerSize[sizeType]">
                            <option value="px" <?=$selected['sizeType']['px']?>>pixel</option>
                            <option value="%" <?=$selected['sizeType']['%']?>>%</option>
                        </select>

                    </label>
                    <label class="vertical-label">
                        X 세로크기 :
                        <input type="text" name="bannerSize[height]" value="<?php echo $data['bannerSize']['height']; ?>" class="form-control js-number width-2xs center" data-number="4, 2000, 300" /> pixel
                    </label>
                </div>
                <div class="notice-info notice-sm">가로 사이즈를 %로 설정할 경우, 세로사이즈는 가로사이즈에 따라 자동 비율 조정되어 노출됩니다. </div>
                <div class="notice-info notice-sm">여러 장의 이미지는 동일한 사이즈로 등록해주세요. 다른 사이즈의 이미지 등록 시 배너가 틀어져 보일 수 있습니다.</div>
                <div class="notice-danger notice-sm">모바일에서 사용시 배너 사이즈는 100%로 처리가 됩니다. </div>
            </td>
        </tr>
        <tr>
            <th class="require">배너 이미지</th>
            <td>
                <div class="form-inline">
                    <table id="table-banner" class="table table-cols" style="margin:0;">
                        <colgroup>
                            <col class="width-4xs" />
                            <col class="width-2xs" />
                            <col class="width-xl" />
                            <col />
                            <col class="width-sm" />
                            <col class="width300" />
                        </colgroup>
                        <tr>
                            <th colspan="6">
                                <div class="form-inline">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                            맨아래
                                        </button>
                                        <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                                            아래
                                        </button>
                                        <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                                            위
                                        </button>

                                        <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                                            맨위
                                        </button>
                                    </div>
                                    <div style="float:right">
                                        <input type="button" value="+ 배너 추가" class="btn btn-white btn-add">
                                        <input type="button" value="선택삭제" class="btn btn-white btn-del">
                                    </div>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class="center"><input type="checkbox" name="allChk" value="y"></th>
                            <th class="center">순서</th>
                            <th class="center">이미지</th>
                            <th class="center">배너등록/링크/이미지설명</th>
                            <th class="center">노출여부</th>
                            <th class="center">노출기간</th>
                        </tr>
                        <?php foreach ($data['bannerInfo'] as $iKey => $iVal) {?>
                            <tr class="banner-info">
                                <td class="center"><input type="checkbox" name="chk[]" value="<?php echo $iKey; ?>"><input type="hidden" name="checkKey[]" value="<?php echo $iKey; ?>"></td>
                                <td class="center"><?php echo $iKey + 1; ?></td>
                                <td class="center">
                                    <?php if (empty($iVal['bannerImage']) === false) {?>
                                        <div class="bannerImage"><?php echo gd_html_banner_image($data['bannerImagePath'] . $data['bannerFolder'] . '/' . $iVal['bannerImage'], $iVal['bannerImageAlt'], 120); ?></div>
                                    <?php }?>
                                </td>
                                <td class="form-inline">
                                    <div class="bannerImageFile mgl15">
                                        <input type="file" name="bannerImageFile[]" value="" class="form-control width60p"/>
                                        <input type="hidden" name="bannerImage[]" value="<?php echo $mode == 'registerSliderBanner' ? '' : $iVal['bannerImage'];?>" />
                                    </div>
                                    <div class="mgt3 mgl15">
                                        <span class="display-inline-block" style="width:75px;">링크 주소 </span>
                                        <input type="text" name="bannerLink[]" value="<?php echo $iVal['bannerLink']; ?>" class="form-control width-xl"/>
                                        <select name="bannerTarget[]" class="form-control">
                                            <option value="">↓ 타겟을 선택하세요.</option>
                                            <option value="" selected="selected">현재창</option>
                                            <?php
                                            foreach ($bannerTargetKindFl as $tKey => $tVal) {
                                                $selected = '';
                                                if ($iVal['bannerTarget'] === $tKey) {
                                                    $selected = ' selected="selected"';
                                                }
                                                echo '<option value="' . $tKey . '" ' . $selected . '>' . $tVal . '</option>' . PHP_EOL;
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mgt3 mgl15">
                                        <span class="display-inline-block" style="width:75px;">이미지 설명</span>
                                        <input type="text" name="bannerImageAlt[]" value="<?php echo $iVal['bannerImageAlt']; ?>" maxlength="50" class="form-control js-maxlength width-xl"/>
                                    </div>
                                    <div class="banner-nav act mgt3 mgl15 display-none">
                                        <span class="display-inline-block" style="width:155px;">네비게이션 활성 버튼</span>
                                        <span>
                                        <input type="file" name="bannerNavActiveImageFile[]" value="" class="form-control width60p"/>
                                        <input type="hidden" name="bannerNavActiveImageTmp[]" value="<?php echo $iVal['bannerNavActiveImageTmp'];?>" />
                                        <input type="hidden" name="bannerNavActiveImage[]" value="<?php echo $iVal['bannerNavActiveImage'];?>" />
                                        <input type="hidden" name="bannerNavActiveW[]" value="<?php echo $iVal['bannerNavActiveW'];?>" />
                                        <input type="hidden" name="bannerNavActiveH[]" value="<?php echo $iVal['bannerNavActiveH'];?>" />
                                    </span>
                                        <?php if (empty($iVal['bannerNavActiveImage']) === false) { ?>
                                            <label>
                                                <img src="<?php echo $iVal['bannerNavActiveImageTmp'];?>" width="50">
                                                <input type="checkbox" name="bannerNavActiveDel[]" value="<?php echo $iVal['bannerNavActiveImage'];?>">삭제
                                            </label>
                                        <?php } ?>
                                    </div>
                                    <div class="banner-nav inact mgt3 mgl15 display-none">
                                        <span class="display-inline-block" style="width:155px;">네비게이션 비활성 버튼</span>
                                        <span>
                                        <input type="file" name="bannerNavInactiveImageFile[]" value="" class="form-control width60p"/>
                                        <input type="hidden" name="bannerNavInactiveImageTmp[]" value="<?php echo $iVal['bannerNavInactiveImageTmp'];?>" />
                                        <input type="hidden" name="bannerNavInactiveImage[]" value="<?php echo $iVal['bannerNavInactiveImage'];?>" />
                                        <input type="hidden" name="bannerNavInactiveW[]" value="<?php echo $iVal['bannerNavInactiveW'];?>" />
                                        <input type="hidden" name="bannerNavInactiveH[]" value="<?php echo $iVal['bannerNavInactiveH'];?>" />
                                    </span>
                                        <?php if (empty($iVal['bannerNavInactiveImage']) === false) { ?>
                                            <label>
                                                <img src="<?php echo $iVal['bannerNavInactiveImageTmp'];?>" width="50">
                                                <input type="checkbox" name="bannerNavInactiveDel[]" value="<?php echo $iVal['bannerNavInactiveImage'];?>">삭제
                                            </label>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <label class="radio-inline"><input type="radio" name="bannerImageUseFl_<?php echo $iKey; ?>" id="bannerImageUseFl_<?php echo $iKey; ?>" value="y" <?php if ($iVal['bannerImageUseFl'] != 'n') { ?> checked="checked" <?php } ?> />노출함</label>
                                    </div>
                                    <div>
                                        <label class="radio-inline"><input type="radio" name="bannerImageUseFl_<?php echo $iKey; ?>" id="bannerImageUseFl_<?php echo $iKey; ?>" value="n" <?php if ($iVal['bannerImageUseFl'] == 'n') { ?> checked="checked" <?php } ?> />노출안함</label>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="pdl10">
                                            <label class="radio-inline"><input type="radio" name="bannerImagePeriodFl_<?php echo $iKey; ?>" value="n" <?php if ($iVal['bannerImagePeriodFl'] != 'y') { ?> checked="checked" <?php } ?> />상시노출</label>
                                            <label class="radio-inline"><input type="radio" name="bannerImagePeriodFl_<?php echo $iKey; ?>" value="y" <?php if ($iVal['bannerImagePeriodFl'] == 'y') { ?> checked="checked" <?php } ?> />기간노출</label>
                                        </span>

                                        <div id="bannerImagePeriodDate<?php echo $iKey; ?>" class="form-inline mgt10 display-none">
                                            <div class="pdl5 mg5">
                                                시작일 :
                                                <span class="input-group js-datetimepicker">
                                                    <input type="text" name="bannerImagePeriodSDate[]" id="bannerImagePeriodSDate_<?php echo $iKey; ?>" value="<?php echo $iVal['bannerImagePeriodSDate']; ?>" class="form-control width-md" placeholder="시작일자입력">
                                                    <span class="input-group-addon">
                                                        <span class="btn-icon-calendar">
                                                        </span>
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="pdl5 mg5">
                                                종료일 :
                                                <span class="input-group js-datetimepicker">
                                                    <input type="text" name="bannerImagePeriodEDate[]" id="bannerImagePeriodEDate_<?php echo $iKey; ?>" value="<?php echo $iVal['bannerImagePeriodEDate']; ?>" class="form-control width-md" placeholder="종료일자입력">
                                                    <span class="input-group-addon">
                                                        <span class="btn-icon-calendar">
                                                        </span>
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        <tfoot>
                        <tr>
                            <th colspan="6">
                                <div class="form-inline">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                            맨아래
                                        </button>
                                        <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                                            아래
                                        </button>
                                        <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                                            위
                                        </button>

                                        <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                                            맨위
                                        </button>
                                    </div>
                                    <div style="float:right">
                                        <input type="button" value="+ 배너 추가" class="btn btn-white btn-add">
                                        <input type="button" value="선택삭제" class="btn btn-white btn-del">
                                    </div>
                                </div>
                            </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="form-inline" style="margin-top:15px; margin-bottom:5px;">
                    <span style="font-size: 12px; font-weight: bold; height: 43px;">배너이미지 노출일괄설정</span>
                    <span class="notice-info notice-sm" style="margin-left: 20px">일괄설정 할 배너이미지를 선택하여주세요.</span>
                </div>
                <div class="form-inline" style="vertical-align: middle;">
                    <span style="width:90%;" class="btn-group">
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width-xs" />
                                <col />
                            </colgroup>
                            <tr>
                                <th class="center">노출여부</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="bannerImageUseFlAll" value="y" checked />노출함</label>
                                    <label class="radio-inline"><input type="radio" name="bannerImageUseFlAll" value="n" />노출안함</label>
                                </td>
                            </tr>
                            <tr>
                                <th class="center">노출기간</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="bannerImagePeriodFlAll" value="n" checked />상시노출</label>
                                    <label class="radio-inline"><input type="radio" name="bannerImagePeriodFlAll" value="y" />기간노출</label>

                                    <span id="bannerImagePeriodDate" class="form-inline mgt10 display-none">
                                        <span class="pdl5 mg5">
                                            시작일 :
                                            <span class="input-group js-datetimepicker">
                                                <input type="text" id="bannerImagePeriodSDateAll" value="" class="form-control width-md" placeholder="시작일자입력">
                                                <span class="input-group-addon">
                                                    <span class="btn-icon-calendar">
                                                    </span>
                                                </span>
                                            </span>
                                        </span>
                                        ~
                                        <span class="pdl5 mg5">
                                            종료일 :
                                            <span class="input-group js-datetimepicker">
                                                <input type="text" id="bannerImagePeriodEDateAll" value="" class="form-control width-md" placeholder="종료일자입력">
                                                <span class="input-group-addon">
                                                    <span class="btn-icon-calendar">
                                                    </span>
                                                </span>
                                            </span>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </span>
                    <span style="float:right;">
                        <input type="button" value="적용" style="margin-top:25px" class="btn btn-black btn-2xl" id="applyBannerAll">
                    </span>
                </div>
            </td>
        </tr>
    </table>
    <div class="notice-danger notice-sm">사용하지 않는 배너 이미지는 삭제하시는 것을 권장합니다.</div>
    <div class="notice-info notice-sm">배너 이미지의 이미지 설명은 해당 이미지의 alt, title 속성의 내용으로 입력되며, 최대 50자 까지 입력 가능합니다.</div>
    <div class="notice-info notice-sm nav-info display-none">네비게이션 버튼 이미지를 직접 등록할 경우, 등록된 이미지 사이즈 그대로 출력이 됩니다.</div>
</form>

<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/slick.js"></script>
<link type="text/css" href="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/slick.css" rel="stylesheet"/>
<script type="text/javascript">
    <!--
    var slider;
    var bannerNav = {};
    var bannerFileName = ['bannerImageFile[]', 'bannerNavActiveImageFile[]', 'bannerNavInactiveImageFile[]'];
    $(document).ready(function () {
        var frmObj = $('#frmBanner');
        verticalLabelHide();

        // 움직이는 배너 정보 저장
        frmObj.validate({
            submitHandler: function (form) {

                var imageCnt = 0;
                $("#bannerImage input[name='bannerImageFile[]']").each(function () {
                    if($(this).val() !='' || $(this).closest('.bannerImageFile').find('input[name="bannerImage[]"]').val() !='') imageCnt++;
                });

                if(imageCnt == $("#bannerImage input[name='bannerImageFile[]']").length || imageCnt == $("#bannerImage input[name='bannerImage[]']").length ) {
                    form.target = 'ifrmProcess';
                    form.submit();
                } else {
                    alert("움직이는 배너 이미지를 등록해주세요.");
                    return false;
                }


            },
            rules: {
                groupSno: "required",
                bannerTitle : "required",
                bannerImageAlt: "required"
            },
            messages: {
                groupSno: {
                    required: '움직이는 배너 그룹을 선택해 주세요.'
                },
                bannerTitle: {
                    required: '움직이는 배너 제목을 입력해주세요.'
                },
                bannerImageAlt: {
                    required: '움직이는 배너 이미지 설명을 입력해 주세요.'
                }
            }
        });

        <?php if ($mode === 'registerSliderBanner') {?>
        // 구분에 따른 스킨 출력
        $('#frmBanner input:radio[name=bannerDeviceType]').each(function () {
            setBannerSkinChange();
        });
        $('#frmBanner input:radio[name=bannerDeviceType]').click(function () {
            setBannerSkinChange();
        });
        <?php } else {?>
        setDeviceType($('input[name="bannerDeviceType"]').val());
        <?php }?>

        // 기간별 노출 설정 여부
        $('input[name=\'bannerPeriodOutputFl\']', frmObj).each(setBannerToggle);
        $('input[name=\'bannerPeriodOutputFl\']', frmObj).click(setBannerToggle);

        $('input[name=\'sideButton[useFl]\']', frmObj).each(setBannerToggle);
        $('input[name=\'sideButton[useFl]\']', frmObj).click(setBannerToggle);

        $('input[name=\'pageButton[useFl]\']', frmObj).each(setBannerToggle);
        $('input[name=\'pageButton[useFl]\']', frmObj).click(setBannerToggle);

        $(document).on('click', 'input[name^=\'bannerImagePeriodFl_\']', function () {
            setBannerToggle();
        });

        $('input[name=\'bannerPeriodOutputFl\']', frmObj).each(setBannerToggle);
        $('input[name=\'bannerPeriodOutputFl\']', frmObj).click(setBannerToggle);

        $('input[name=\'bannerImagePeriodFlAll\']', frmObj).each(setBannerToggle);
        $('input[name=\'bannerImagePeriodFlAll\']', frmObj).click(setBannerToggle);

        $('.swiper-wrapper').slick({
            draggable : false,
            <?php if ($data['bannerSliderConf']['time'] == 'manual') { ?>
            autoplay: false,
            <?php } else { ?>
            autoplay: true,
            autoplaySpeed :  <?php echo $data['bannerSliderConf']['time'] * 1000; ?>,
            speed: <?php echo $data['bannerSliderConf']['speed']; ?>,
            <?php } ?>
            <?php if ($data['pageButton']['useFl'] == 'n') { ?>
            dots: false,
            <?php } else { ?>
            dots: true,
            <?php } ?>
            infinite: true,
            <?php if ($data['bannerSliderConf']['effect'] === 'fade') {?>
            fade: true,
            <?php } ?>
            prevArrow: $('.swiper-button-prev'),
            nextArrow: $('.swiper-button-next'),
            slidesToShow: 1,
            adaptiveHeight: true
        });

        <?php if ($data['sideButton']['useFl'] !== 'y') {?>
        $('.swiper-button-prev').hide();
        $('.swiper-button-next').hide();
        <?php } ?>
        <?php if ($data['pageButton']['useFl'] !== 'y') {?>
        $('.swiper-pagination').hide();
        <?php } ?>
        $(document).on('change blur', 'select[name^=\'bannerSliderConf\'], input[name^=\'bannerSliderConf\'], input[name^=\'sideButton\'], input[name^=\'pageButton\'], input[name^=\'bannerSize\'], input[name^=\'bannerImageFile\'], input[name^=\'bannerNav\'], select[name=\'bannerSize[sizeType]\']', function () {
            var bannerName = this.name;
            var bannerType = this.type;

            $('.swiper-wrapper').slick('unslick'); /* ONLY remove the classes and handlers added on initialize */

            if (bannerType == 'file') {
                var idx = $('input[name=\'' + bannerName + '\']').index(this);
                var file = $(this).prop('files')[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        if (bannerName == 'bannerImageFile[]') {
                            var $previewTarget = $('.slick-slide').not('.slick-cloned').eq(idx);
                            $previewTarget.html('<img src="' + e.target.result + '"/>');
                        } else {
                            var image = new Image();
                            image.src = e.target.result;

                            image.onload = function() {
                                if (bannerName == 'bannerNavActiveImageFile[]') {
                                    var bannerNavName = 'bannerNavActive';
                                } else {
                                    var bannerNavName = 'bannerNavInactive';
                                }
                                $('input[name="' + bannerNavName + 'ImageTmp[]"]').eq(idx).val(e.target.result);
                                $('input[name="' + bannerNavName + 'W[]"]').eq(idx).val(this.width);
                                $('input[name="' + bannerNavName + 'H[]"]').eq(idx).val(this.height);
                            }
                        }
                    }
                    reader.readAsDataURL(file);
                }
            }

            previewSliderBanner();
        });

        $('.btn-add').click(function(){
            var len = $('.banner-info').length + 1;

            addBanner(len);
            setButtonCustom();
        });

        $('.btn-del').click(function(){
            var len = $(this).closest('table').find('.banner-info input[name="chk[]"]:checked').length;
            var bannerLen = $(this).closest('table').find('.banner-info').length;
            if (len <= 0) {
                alert('선택된 배너가 없습니다.');
                return false;
            }

            if (len == bannerLen) {
                addBanner(0);
            }

            var incre = 0;
            $(this).closest('table').find('.banner-info input[name="chk[]"]').each(function(index) {
                if (this.checked === true) {
                    $('.swiper-wrapper').slick('slickRemove',index - incre);
                    $(this).closest('tr').remove();
                    incre++;
                }
            });

            bannerSortNum();
            setButtonCustom();
        });

        function addBanner(len) {
            var orgRadioName1 = '';
            var orgRadioName2 = '';
            var orgRadioValue1 = '';
            var orgRadioValue2 = '';

            $('input[name^="bannerImageUseFl_"]').each(function(){
                if (orgRadioName1 == '') {
                    orgRadioName1 = $(this).attr('name');
                    orgRadioValue1 = $('input[name="' + orgRadioName1 + '"]:checked').val();
                }
            });
            $('input[name^="bannerImagePeriodFl_"]').each(function(){
                if (orgRadioName2 == '') {
                    orgRadioName2 = $(this).attr('name');
                    orgRadioValue2 = $('input[name="' + orgRadioName2 + '"]:checked').val();
                }
            });
            var trueKey = len - 1;
            var html = $('.banner-info:eq(0)')[0].outerHTML;
            $('.btn-add').closest('table').append(html);

            var $target = $('#table-banner tbody').find('tr').last();
            $target.find('td').eq(1).html(len);

            // input file 를 삭제
            $target.find('.bannerImage').remove();
            $target.find('.bannerImageFile input').remove();
            $target.find('.bannerImageFile div').remove();
            $target.find('.banner-nav input').remove();
            $target.find('.banner-nav div').remove();

            var addHtml = '';
            var bannerActHtml = '';
            var bannerInactHtml = '';
            addHtml = '<input type="file" name="bannerImageFile[]" value="" class="form-control width80p"/>';
            addHtml = addHtml + '<input type="hidden" name="bannerImage[]" value="" />';
            $target.find('.bannerImageFile').append(addHtml);

            bannerActHtml = '<input type="file" name="bannerNavActiveImageFile[]" value="" class="form-control width60p"/>';
            bannerActHtml += '<input type="hidden" name="bannerNavActiveImage[]" value="" />';
            $target.find('.banner-nav.act span:eq(1)').append(bannerActHtml);

            bannerInactHtml = '<input type="file" name="bannerNavInactiveImageFile[]" value="" class="form-control width60p"/>';
            bannerInactHtml += '<input type="hidden" name="bannerNavInactiveImage[]" value="" />';
            $target.find('.banner-nav.inact span:eq(1)').append(bannerInactHtml);
            init_file_style();

            // input 값을 초기화함
            $target.find('input[name="chk[]"]').val(trueKey);
            $target.find('input[name="checkKey[]"]').val(trueKey);
            $target.find('input[name="bannerImage[]"]').val('');
            $target.find('input[name="bannerLink[]"]').val('');
            $target.find('input[name="bannerImageAlt[]"]').val('');
            $target.find('input[name^="bannerImageUseFl_"]').each(function(){
                $(this).attr({'name' : 'bannerImageUseFl_' + trueKey , 'id' : 'bannerImageUseFl_' + trueKey});
            });
            $target.find('input[name^="bannerImagePeriodFl_"]').each(function(){
                $(this).attr({'name' : 'bannerImagePeriodFl_' + trueKey});
            });
            $target.find('div[name^="bannerImagePeriodFl_"]').each(function(){
                $(this).attr({'name' : 'bannerImagePeriodFl_' + trueKey});
            });
            $target.find('div[id^="bannerImagePeriodDate"]').prop({'id' : 'bannerImagePeriodDate' + trueKey});
            $target.find('input[id^="bannerImagePeriodSDate_"]').prop({'id' : 'bannerImagePeriodSDate_' + trueKey});
            $target.find('input[id^="bannerImagePeriodEDate_"]').prop({'id' : 'bannerImagePeriodEDate_' + trueKey});

            $('input:radio[name=' + orgRadioName1 + ']:input[value=' + orgRadioValue1 + ']').prop('checked', true);
            $('input:radio[name=' + orgRadioName2 + ']:input[value=' + orgRadioValue2 + ']').prop('checked', true);
            $('input:radio[name=bannerImageUseFl_' + trueKey + ']:input[value=y]').prop('checked', true);
            $('input:radio[name=bannerImagePeriodFl_' + trueKey + ']:input[value=n]').prop('checked', true);
            $('div[id=bannerImagePeriodDate' + trueKey + ']').hide();
            $('input[id=bannerImagePeriodSDate_' + trueKey + ']').val('');
            $('input[id=bannerImagePeriodEDate_' + trueKey + ']').val('');

            if (len == 0) {
                $('.swiper-wrapper').slick('slickAdd','<div><img src="/admin/gd_share/img/godo5_banner_01.jpg"></div>');
            } else {
                $('.swiper-wrapper').slick('slickAdd','<div></div>');
            }

            init_datetimepicker();
        }

        $('.js-moverow').click(function(){
            init_file_style_destroy();
            var $table = $(this).closest('table'), len = $(this).closest('table').find('.banner-info input[name="chk[]"]:checked').length;
            var maxLen = $(this).closest('table').find('.banner-info').length, direction = $(this).data('direction');
            var idx = 0, $target = $(this).closest('table').find('.banner-info input[name="chk[]"]:checked');
            if (len <= 0) {
                alert('순서 변경을 원하시는 배너를 선택해주세요.');
                return false;
            }
            if (direction == 'down') $target = $($(this).closest('table').find('.banner-info input[name="chk[]"]:checked').get().reverse());

            $target.each(function(){
                var $tr = $(this).closest('tr');
                var index = $tr.index('.banner-info');

                switch (direction) {
                    case 'bottom':
                        $table.append($tr.detach());
                        break;
                    case 'down':
                        if (index < maxLen - 1) {
                            $tr.next().after($tr.detach());
                        }
                        break;
                    case 'up':
                        if (index > 0) {
                            $tr.prev().before($tr.detach());
                        }
                        break;
                    case 'top':
                        if (index == 0 || index === null) {
                            break;
                        } else {
                            $table.find('.banner-info:eq(' + idx + ')').before($tr.detach());
                            idx++;
                            break;
                        }
                }
            });

            sliderBannerImageReset();
            bannerSortNum();
            init_file_style();

            for (var i in bannerFileName) {
                $('input[name="' + bannerFileName[i] + '"]').each(function(index) {
                    if (this.value) {
                        var id = this.id;
                        var fileName = this.value.split('\\').pop();
                        $('label[for="' + id + '"]').closest('div').find('input').val(fileName);
                    }
                });
            }
        });

        $('input[name="allChk"]').click(function(){
            var checked = $(this).prop('checked');

            $(this).closest('table').find('input[name="chk[]"]').prop('checked', checked);
        });

        $('select[name="bannerSize[sizeType]"]').change(function(){
            verticalLabelHide();
        });

        /**
         * 토글 설정 여부
         */
        function setBannerToggle() {
            var divId = '';
            if ($(this).attr('name') == 'bannerPeriodOutputFl') {
                divId = 'bannerPeriodDate';
            }
            if ($(this).attr('name') == 'sideButton[useFl]') {
                divId = 'bannerSideButtonConf';
            }
            if ($(this).attr('name') == 'bannerImagePeriodFlAll') {
                divId = 'bannerImagePeriodDate';
            }
            if ($(this).attr('name') == 'pageButton[useFl]') {
                divId = 'bannerPageButtonConf';
                if (this.checked === true) {
                    switch ($(this).val()) {
                        case 'y':
                        case 'n':
                            $('.banner-nav, .nav-info').addClass('display-none');
                            break;
                        case 'c':
                            $('.banner-nav, .nav-info').removeClass('display-none');
                            break;
                    }
                }
            }
            if (divId != '') {
                if (this.checked === true && ($(this).val() == 'n' || $(this).val() == 'c')) {
                    $('#' + divId).hide();
                } else if (this.checked === true && $(this).val() == 'y') {
                    $('#' + divId).show();
                }
            }
            // 배너 이미지의 노출기간은 그냥 무조건 체크 하는방식으로 처리함
            $.each($("[name^='bannerImagePeriodFl_']"), function () {
                var tempName = $(this).attr('name');
                var arrName = tempName.split('_');
                if ($('input[name="bannerImagePeriodFl_' + arrName[1] + '"]:checked').val() == 'y') {
                    $('#bannerImagePeriodDate' + arrName[1]).show();
                } else {
                    $('#bannerImagePeriodDate' + arrName[1]).hide();
                }
            });
        }

        function bannerSortNum() {
            $('.banner-info').each(function(index) {
                $(this).find('td:eq(1)').html(index + 1);
            });
        }

        <?php if (empty($data['sno']) === false) { ?>
        $('input[name="bannerSize[width]"]').blur();
        <?php } ?>
    });


    <?php if ($mode === 'registerSliderBanner') {?>
    /**
     * 구분에 따른 스킨 출력
     */
    function setBannerSkinChange() {
        var frontSkinOption = '<?php echo implode('', $skinList['front']);?>';
        var mobileSkinOption = '<?php echo implode('', $skinList['mobile']);?>';
        var selectDeviceType = $('#frmBanner input:radio[name=bannerDeviceType]:checked').val();
        var selectSkinOption = '<option value="">↓ 디자인 스킨을 선택하세요.</option>';

        // 구분 선택에 따른 select option 값
        if (selectDeviceType == 'front') {
            selectSkinOption = selectSkinOption + frontSkinOption;
        } else {
            selectSkinOption = selectSkinOption + mobileSkinOption;
        }

        // select option 처리
        $('#frmBanner select[name=skinName]').html(selectSkinOption);

        setDeviceType(selectDeviceType);
    }
    <?php } ?>

    function setDeviceType(device)
    {
        switch (device) {
            case 'front':
                $('input[name="bannerSize[width]"]').prop('readonly', false);
                $('select[name="bannerSize[sizeType]"]').find('option:eq(0)').prop('disabled', false);
                break;
            case 'mobile':
                $('input[name="bannerSize[width]"]').val('100').prop('readonly', true);
                $('select[name="bannerSize[sizeType]"]').find('option:eq(0)').prop('disabled', true);
                $('select[name="bannerSize[sizeType]"]').val('%');
                break;
        }
        verticalLabelHide();
        $('input[name="bannerSize[width]"]').blur();
    }

    function previewSliderBanner()
    {
        var sliderSetting = {};
        var speed = $('select[name=\'bannerSliderConf[speed]\']').val(); //전환 속도
        var time = $('select[name=\'bannerSliderConf[time]\']').val(); //전환 시간
        var effect = $('input[name=\'bannerSliderConf[effect]\']:checked').val(); //효과
        var sideUseFl = $('input[name=\'sideButton[useFl]\']:checked').val(); //좌우 전환 버튼 사용 여부
        var sideActiveColor = $('input[name=\'sideButton[activeColor]\']').val(); //좌우 전환 버튼 색상
        var pageUseFl = $('input[name=\'pageButton[useFl]\']:checked').val(); //네비게이션 사용 여부
        var pageRadius = $('input[name=\'pageButton[radius]\']:checked').val(); //네비게이션 종류
        var pageActiveColor = $('input[name=\'pageButton[activeColor]\']').val(); //네비게이션 활성 색상
        var pageInactiveColor = $('input[name=\'pageButton[inactiveColor]\']').val(); //네비게이션 비활성 색상
        var pageSize = $('input[name=\'pageButton[size]\']:checked').val(); //네비게이션 크기
        var width = $('input[name=\'bannerSize[width]\']').val(); //배너 가로사이즈
        var height = $('input[name=\'bannerSize[height]\']').val(); //배너 세로사이즈
        var sizeType = $('select[name=\'bannerSize[sizeType]\']').val(); //배너 가로사이즈

        if ((!width || width == 0) || (!height || height == 0)) {
            $('#preview-area').hide();
            return false;
        } else {
            $('#preview-area').show();
        }

        var setting = {
            draggable : false,
            infinite: true,
            slidesToShow: 1,
            adaptiveHeight: true
        };

        if (time == 'manual') {
            setting['autoplay'] = false;
        } else {
            setting['autoplay'] = true;
            setting['speed'] = speed;
            setting['autoplaySpeed'] = time * 1000;
        }

        if (sideUseFl == 'y') {
            $('.swiper-button-prev').css('background-image', "url(\"data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2027%2044'%3E%3Cpath%20d%3D'M0%2C22L22%2C0l2.1%2C2.1L4.2%2C22l19.9%2C19.9L22%2C44L0%2C22L0%2C22L0%2C22z'%20fill%3D'%23" + sideActiveColor.replace('#', '') + "'%2F%3E%3C%2Fsvg%3E\")");
            $('.swiper-button-next').css('background-image', "url(\"data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2027%2044'%3E%3Cpath%20d%3D'M27%2C22L27%2C22L5%2C44l-2.1-2.1L22.8%2C22L2.9%2C2.1L5%2C0L27%2C22L27%2C22z'%20fill%3D'%23" + sideActiveColor.replace('#', '') + "'%2F%3E%3C%2Fsvg%3E\")");

            setting['prevArrow'] = $('.swiper-button-prev');
            setting['nextArrow'] = $('.swiper-button-next');

            $('.swiper-button-prev').show();
            $('.swiper-button-next').show();
        } else {
            setting['arrows'] = false;
            $('.swiper-button-prev').hide();
            $('.swiper-button-next').hide();
        }

        var swiperContainerH = 'height:' + height + 'px !important;';
        if (sizeType == '%') {
            if (width > 100) width = 100;
            width = 6 * width;
            swiperContainerH = 'height:auto !important;';
        }

        var stylesheet = '.swiper-container, .swiper-wrapper, .swiper-wrapper .slick-slide img {width:' + width + 'px !important; ' + swiperContainerH + '}';

        stylesheet += '.swiper-slide {' + swiperContainerH + '}';

        if (pageUseFl == 'y') {

            setting['dots'] = true;

            $('#pager-style').remove();
            $("head").append('<style id="pager-style" type="text/css"></style>');
            var new_stylesheet = $("head").children(':last');
            new_stylesheet.html(
                stylesheet +
                ' .swiper-wrapper .slick-dots li {margin:0 5px !important;}' +
                ' .swiper-wrapper  .slick-dots li button {font-size:0 !important; line-height:0 !important; width:' + pageSize + 'px !important; height:' + pageSize + 'px !important; border-radius:' + pageRadius + '% !important; background:' + pageInactiveColor + ' !important;}' +
                ' .swiper-wrapper .slick-dots li.slick-active button{background:' + pageActiveColor + ' !important;}'
            );
        } else if (pageUseFl == 'c') {
            setting['dots'] = true;

            $('#pager-style').remove();
            $("head").append('<style id="pager-style" type="text/css"></style>');
            var buttonWidth = '12.5';
            if ($('input[name="bannerDeviceType"]:checked').val() == 'mobile' || $('input[name="bannerDeviceType"]').val() == 'mobile') {
                buttonWidth = '25';
            }
            $("head #pager-style").html(
                stylesheet +
                ' .swiper-wrapper .slick-dots li {width:' + buttonWidth + '%; margin:0 !important;}' +
                ' .swiper-wrapper .slick-dots li button, .swiper-wrapper .slick-dots li button {font-size:12px !important; width:100% !important; height:30px !important; text-align:center;  background:#000000 !important; border-radius:0 !important; color:#fff; opacity:1 !important;}' +
                ' .swiper-wrapper .slick-dots li.slick-active button{background:#cfcfcf !important;}'
            );
        } else {
            setting['dots'] = false;
        }

        if (effect == 'fade') setting['fade'] = true;

        $('.swiper-wrapper').slick(setting);

        setButtonCustom();
    }

    var setButtonCustom = function() {
        var pageUseFl = $('input[name="pageButton[useFl]"]:checked').val();
        if (pageUseFl != 'c') return;

        var html = '';
        $('.swiper-wrapper .slick-dots li').each(function(index){
            if ($('input[name="bannerNavActiveImageTmp[]"]').eq(index).val()) {
                html += '.swiper-wrapper  .slick-dots li.slick-active#' + this.id + ' {width:' + $('input[name="bannerNavActiveW[]"]').eq(index).val() + 'px !important; height:' + $('input[name="bannerNavActiveH[]"]').eq(index).val() + 'px !important;}' +
                    '.swiper-wrapper  .slick-dots li.slick-active#' + this.id + ' button {background:url("' + $('input[name="bannerNavActiveImageTmp[]"]').eq(index).val() + '") no-repeat !important; width:' + $('input[name="bannerNavActiveW[]"]').eq(index).val() + 'px !important; height:' + $('input[name="bannerNavActiveH[]"]').eq(index).val() + 'px !important; font-size:0 !important;}';
            }
            if ($('input[name="bannerNavInactiveImageTmp[]"]').eq(index).val()) {
                html += '.swiper-wrapper  .slick-dots li#' + this.id + ':not(.slick-active) {width:' + $('input[name="bannerNavInactiveW[]"]').eq(index).val() + 'px !important; height:' + $('input[name="bannerNavInactiveH[]"]').eq(index).val() + 'px !important;}' +
                    '.swiper-wrapper  .slick-dots li#' + this.id + ':not(.slick-active) button {background:url("' + $('input[name="bannerNavInactiveImageTmp[]"]').eq(index).val() + '") no-repeat !important; width:' + $('input[name="bannerNavInactiveW[]"]').eq(index).val() + 'px !important; height:' + $('input[name="bannerNavInactiveH[]"]').eq(index).val() + 'px !important; font-size:0 !important;}';
            }
        });

        $('#button-style').remove();
        $("head").append('<style id="button-style" type="text/css"></style>');
        $("head #button-style").html(html);
    };

    function sliderBannerImageReset()
    {
        var bannerIndex = [];
        var bannerImage = [];
        $('#table-banner').find('.banner-info').each(function(){
            bannerIndex.push($(this).find('td:eq(1)').html() - 1);
        });

        for (var i in bannerIndex) {
            var $imageSrc = $('.slick-slide[data-slick-index=' + bannerIndex[i] + ']').not('.slick-cloned').find('img').prop('src');
            if ($imageSrc) {
                bannerImage.push('<div><img src="' + $imageSrc + '"></div>');
            } else {
                bannerImage.push('<div></div>');
            }
        }
        $('.swiper-wrapper').slick('removeSlide', null, null, true);

        for (var i in bannerImage) {
            $('.swiper-wrapper').slick('slickAdd', bannerImage[i]);
        }

        $('input[name="bannerSize[width]"]').blur();
    }

    function verticalLabelHide() {
        var sizeType = $('select[name="bannerSize[sizeType]"]').val();

        switch (sizeType) {
            case 'px':
                $('.vertical-label').show();
                break;
            case '%':
                $('.vertical-label').hide();
                break;
        }
    }

    $('#applyBannerAll').click(function(){
        var len = $(this).closest('table').find('.banner-info input[name="chk[]"]:checked').length;
        if (len <= 0) {
            alert('일괄설정 할 배너이미지를 선택하여주세요.');
            return false;
        }
        var bannerImageUseFlAll = $('input[name="bannerImageUseFlAll"]:checked').val();
        var bannerImagePeriodFlAll = $('input[name="bannerImagePeriodFlAll"]:checked').val();
        var bannerImagePeriodSDateAll = $('#bannerImagePeriodSDateAll').val();
        var bannerImagePeriodEDateAll = $('#bannerImagePeriodEDateAll').val();
        var thisKey = 0;
        $('input[name="chk[]"]:checked').each(function(){
            thisKey = $(this).val();
            $('input:radio[name=bannerImageUseFl_' + thisKey + ']:input[value=' + bannerImageUseFlAll + ']').prop('checked', true);
            $('input:radio[name=bannerImagePeriodFl_' + thisKey + ']:input[value=' + bannerImagePeriodFlAll + ']').prop('checked', true);
            if (bannerImagePeriodFlAll == 'y') {
                $('#bannerImagePeriodDate' + thisKey).show();
            } else {
                $('#bannerImagePeriodDate' + thisKey).hide();
            }
            $('#bannerImagePeriodSDate_' + thisKey).val(bannerImagePeriodSDateAll);
            $('#bannerImagePeriodEDate_' + thisKey).val(bannerImagePeriodEDateAll);
        });
    });
    //-->
</script>
