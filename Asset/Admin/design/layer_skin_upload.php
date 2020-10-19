<form id="frmSkinUpload" name="frmSkinUpload" action="design_skin_list_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="uploadSkin" />
    <input type="hidden" name="skinType" value="<?php echo $skinType;?>" />

    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">스킨코드</th>
            <td>
                <input type="text" name="uploadSkinCode" maxlength="20" placeholder="스킨코드를 입력하세요. (영문, 숫자, _ 만 입력)" class="form-control width90p" style="ime-mode:disabled;"/>
                <div class="notice-info mgt5">스킨코드는 영문, 숫자, _ 만 입력 하세요. (특수문자, 공백, 한글 입력 금지)</div>
            </td>
        </tr>
        <tr>
            <th>스킨명</th>
            <td><input type="text" name="uploadSkinName" placeholder="스킨명을 입력하세요." class="form-control width90p" maxlength="20"/></td>
        </tr>
        <tr>
            <th class="require">업로드</th>
            <td>
                <input type="file" name="uploadSkin" class="form-control width90p"/>
                <div class="notice-info mgt5">압축파일의 종류는 ZIP 파일만 가능합니다.</div>
                <div class="notice-info mgt5">압축파일의 용량은 <?php echo ini_get('upload_max_filesize');?>미만이여야 정상적으로 업로드가 가능합니다.</div>
            </td>
        </tr>
        <tr>
            <th>썸네일 이미지</th>
            <td>
                <input type="file" name="thumbnails" class="form-control width90p"/>
                <div class="notice-info mgt5">jpg, jpeg, png, gif만 등록 가능하며, 기본 이미지는 150x150 pixel 입니다.</div>
                <div class="notice-info mgt5">이미지명은 되도록이면 영문으로 올려주세요.</div>
            </td>
        </tr>
    </table>
    <div class="text-center">
        <input type="submit" value="스킨 업로드 하기" class="btn btn-red" />
    </div>
</form>

<script type="text/javascript">
    <!--
    // validator 플러그인 직접 선언
    $.validator.addMethod( "alphanumeric", function( value, element ) {
        return this.optional( element ) || /^\w+$/i.test( value );
    }, "Letters, numbers, and underscores only please" );

    function fileNameCheck(inputFileName){

        var agent = navigator.userAgent.toLowerCase();
        os = {
            Windows : /win/.test(agent),
            Mac : /mac/.test(agent),
            Linux : /linux/.test(agent),
            Unix : /x11/.test(agent)
        };
        if(!os.Windows){
            return true;
        }

        var fileValue = $('input[name='+inputFileName+']').val().split("\\");
        var fileName = fileValue[fileValue.length-1];
        var fileNameArry = fileName.split('.');
        var ext = fileNameArry.pop();
        var realName = fileNameArry.join('.');
        if(!/^\w+$/i.test( realName )){
            alert('업로드 파일명은 영문과 숫자만 입력해주세요.');
            return false;
        }else{
            return true;
        }
    }

    $(document).ready(function(){
        // 스킨 업로드
        $("#frmSkinUpload").validate({
            submitHandler: function (form) {
                if(!fileNameCheck('uploadSkin')){
                    return false;
                }
                form.target = 'ifrmProcess';
                form.submit();
            },
            dialog: false,
            rules: {
                uploadSkinCode: {
                    required: true,
                    alphanumeric: true
                },
                uploadSkin: "required"
            },
            messages: {
                uploadSkinCode: {
                    required: '스킨코드를 입력해 주세요.',
                    alphanumeric: '영문과 숫자만 입력해주세요.'
                },
                uploadSkin: {
                    required: '압축 파일(ZIP 파일)을 선택해 주세요.'
                }
            }
        });
    });
    //-->
</script>
