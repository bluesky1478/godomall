<form id="frmHappytalkConfig" name="frmHappytalkConfig" action="../service/happytalk_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="config"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        카카오 상담톡 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="input_title">상담톡 사용설정</th>
            <td class="form-inline">
                <label class="radio-inline">
                    <input type="radio" name="useHappytalkFl" value="y" <?= $checked['useHappytalkFl']['y']; ?> onclick="init_kakao_happytalk('y')"/>사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useHappytalkFl" value="n" <?= $checked['useHappytalkFl']['n']; ?> onclick="init_kakao_happytalk('n')"/>사용안함
                </label>
                <div class="notice-info">
                    서비스를 사용하시려면 카카오 상담톡 서비스 신청 후 사용이 가능합니다. <a href="https://www.godo.co.kr/echost/power/add/member/kakao-counsel.gd" target="_blank" class="btn-link">신청하기</a>
                </div>
            </td>
        </tr>
        <tr>
            <th class="input_title">서비스 적용 범위</th>
            <td class="form-inline">
                <label class="radio-inline">
                    <input type="radio" name="happytalkDeviceType" value="all" <?= $checked['happytalkDeviceType']['all']; ?> <?= $disabled; ?>/>PC + 모바일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="happytalkDeviceType" value="pc" <?= $checked['happytalkDeviceType']['pc']; ?> <?= $disabled; ?>/>PC
                </label>
                <label class="radio-inline">
                    <input type="radio" name="happytalkDeviceType" value="mobile" <?= $checked['happytalkDeviceType']['mobile']; ?> <?= $disabled; ?>/>모바일
                </label>
            </td>
        </tr>
        <tr>
            <th class="input_title require">해피톡 사이트 아이디</th>
            <td class="form-inline">
                <input type="text" name="happytalkID" value="<?= $data['happytalkID']; ?>" class="form-control width-3xl" <?= $readOnly; ?>/>
                <div class="notice-info">
                    해피톡 사이트 아이디는 <a href="https://happytalk.io/" target="_blank" class="btn-link">[해피톡 관리자 페이지]</a>의 채팅 버튼 달기 > 카카오톡 채팅 버튼 달기에서 확인하실 수 있습니다.
                </div>
            </td>
        </tr>
        <tr>
            <th class="input_title require">상담분류 번호</th>
            <td class="form-inline">
                <input type="text" name="happytalkCategoryId" placeholder="대분류(번호만 입력)" value="<?= $data['happytalkCategoryId'] ?>" class="form-control width-lg" <?= $readOnly; ?>/> <input type="text" name="happytalkDivisionId" placeholder="중분류(번호만 입력)" value="<?= $data['happytalkDivisionId'] ?>" class="form-control width-lg" <?= $readOnly; ?>/>
                <div class="notice-info">
                    <a href="https://happytalk.io/" target="_blank" class="btn-link">[해피톡 관리자 페이지]</a>의 상담 분류 관리에서 사용 중인 번호(코드값)를 넣어주세요.
                </div>
            </td>
        </tr>
        <tr>
            <th class="input_title require">플러스친구 아이디</th>
            <td class="form-inline">
                <input type="text" name="kakaoPlusId" placeholder="예) @고도몰" value="<?= $data['kakaoPlusId'] ?>" class="form-control width-sm" <?= $readOnly; ?>/>
                <input type="button" name="kakaoPlusIdBtn" value="카카오 알림톡 플러스친구 아이디 불러오기" class="btn btn-gray btn-sm" <?= $disabled ?>/>
                <div class="notice-info">
                    카카오톡 플러스친구 <label class="text-danger">검색용 아이디</label>를 입력해주세요. @를 앞에 붙여주셔야 합니다. 예) @고도몰
                </div>
                <div class="notice-info">
                    카카오톡 플러스친구 아이디가 없다면 <a href="https://center-pf.kakao.com/login" target="_blank" class="btn-link">[카카오톡 플러스 친구 관리자]</a>에서 발급받은 후 등록해주세요.
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<script type="text/javascript">
    <!--
    $(document).ready(function() {

        $("#frmHappytalkConfig").validate({
            rules: {
                mode: {
                    required: true,
                },
                happytalkID: {
                    required: function () {
                        return $(':radio[name="useHappytalkFl"]:checked').val() == 'y';
                    },
                },
                happytalkCategoryId: {
                    required: function () {
                        return $(':radio[name="useHappytalkFl"]:checked').val() == 'y';
                    },
                },
                happytalkDivisionId: {
                    required: function () {
                        return $(':radio[name="useHappytalkFl"]:checked').val() == 'y';
                    },
                },
                kakaoPlusId: {
                    required: function () {
                        return $(':radio[name="useHappytalkFl"]:checked').val() == 'y';
                    },
                }
            },
            messages: {
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                happytalkID: {
                    required: '해피톡 사이트 아이디를 입력하세요.',
                },
                happytalkCategoryId: {
                    required: '상담분류 번호를 입력하세요.',
                },
                happytalkDivisionId: {
                    required: '상담분류 번호를 입력하세요.',
                },
                kakaoPlusId: {
                    required: '플러스친구 아이디를 입력하세요.',
                }
            }
        });

        $('input[name=kakaoPlusIdBtn]').on('click', function(){
            $('input[name=kakaoPlusId]').val('<?=$kakaoPlusId?>');
        })

        $('input[name^="happytalk"]').number_only('d');

    });

    function init_kakao_happytalk(value) {
        if (value == 'y') {
            $('input[name=happytalkDeviceType]').prop('disabled', false);
            $('input[name=kakaoPlusIdBtn]').prop('disabled', false);
            $('input[type=text]').prop('readonly', false);
        } else {
            $('input[name=happytalkDeviceType]').prop('disabled', true);
            $('input[name=kakaoPlusIdBtn]').prop('disabled', true);
            $('input[type=text]').prop('readonly', true);
        }
    }
    //-->
</script>
