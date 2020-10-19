/*
	한글을 영문발음처럼 고쳐주는 함수

	var s='aa 한글';
	var cs=convertKonglish(s);
	alert(cs); 

	result : aa hankeur
*/
function convertKonglish(str) {

	/* map (이 주석은 지우면 안되요)
	font_cho = Array(
	'ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ',
	'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ', 'ㅅ', 'ㅆ',
	'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ' );

	font_jung = Array(
	'ㅏ', 'ㅐ', 'ㅑ', 'ㅒ', 'ㅓ',
	'ㅔ', 'ㅕ', 'ㅖ', 'ㅗ', 'ㅘ', 'ㅙ',
	'ㅚ', 'ㅛ', 'ㅜ', 'ㅝ', 'ㅞ', 'ㅟ',
	'ㅠ', 'ㅡ', 'ㅢ', 'ㅣ' );

	font_jong = Array(
	'', 'ㄱ', 'ㄲ', 'ㄳ', 'ㄴ', 'ㄵ', 'ㄶ', 'ㄷ', 'ㄹ',
	'ㄺ', 'ㄻ', 'ㄼ', 'ㄽ', 'ㄾ', 'ㄿ', 'ㅀ', 'ㅁ',
	'ㅂ', 'ㅄ', 'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ' );
	*/

	var fontCho = [
	'g', 'gg', 'n', 'd', 'dd',
	'r', 'm', 'b', 'bb', 's', 'ss',
	'', 'j', 'jj', 'ch', 'k', 't', 'p', 'h'];

	var fontJung = [
	'a', 'ae', 'ya', 'yae', 'eo',
	'e', 'yeo', 'ye', 'o', 'wa', 'wae',
	'oe', 'yo', 'u', 'wo', 'we', 'wi',
	'yu', 'eu', 'ui', 'i'];

	var fontJong = [
	'','k', 'kk', 't', 'n', 't', 'n', 'd', 'r',
	'k', 'm', 'p', 't', 't', 'p', 'h', 'm',
	'p', 't', 't', 't', 'ng', 't', 't', 'k', 't', 'p', 'h'];

	var i;l=str.length;
	var letter;
	var returnStr='';
	for(i=0;i<l;i++)
	{
		letter=str.substr(i,1);
		var chkExp=/[가-힣]/;

		if(chkExp.test(letter))
		{
			CompleteCode = letter.charCodeAt(0);
			UniValue = CompleteCode - 0xAC00;

			Jong = UniValue % 28;
			Jung = ( ( UniValue - Jong ) / 28 ) % 21;
			Cho = parseInt (( ( UniValue - Jong ) / 28 ) / 21);

			returnStr+=(fontCho[Cho]+fontJung[Jung]+fontJong[Jong]);
		}
		else
		{
			returnStr+=letter;
		}
	}
	
	return returnStr;

}