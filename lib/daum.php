<?php
	include('../lib/header.php');
?>
<div id='container'>
	<div class='item'>
		<dl>
			<dt>DAUM 일간순위</dt>
			<dd>
				<div class='group' style='padding:0px;'>
<?php
	header('Content-Type: text/html; charset=UTF-8');

	
	$try_count = 3;

	echo "<div style='display: block;width:100%;'><ul style='margin:0;padding:0;overflow: hidden;list-style: none;'>";
	$newurl = "";
	$target = "http://webtoon.daum.net/data/pc/webtoon/list_daily_ranking/serialized";
	$get_html_contents = file_get_html($target);
	for($html_c = 0; $html_c < $try_count; $html_c++){
		if(strlen($get_html_contents) > 10000){
			break;
		} else {
			$get_html_contents = "";
			$get_html_contents = file_get_html($target);
		}
	}

	$data_array = json_decode($get_html_contents, true);

	foreach ($data_array['data'] as $key => $value){
		echo "<li style='box-sizing: border-box;position: relative;float: left; height:190px;width:33%;'><img src='".$value['pcRecommendImage']['url']."' width='100%'>";
		if ($value['latestWebtoonEpisode']['ageGrade']==19) echo "<span style='font-family:NanumGothic;position:absolute;top:2px;left:2px;background-color: #e50020;padding: 1px;color: #fff;border-radius: 20px;font: normal 14px arial;width: 20px;height: 20px;'>19</span>";
		echo "<br><span style='font-size:14px;margin:0;padding:0;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 4;-webkit-box-orient: vertical;word-wrap:break-word; line-height: 1.0em;height: 4.0em;'><a href='http://m.webtoon.daum.net/m/webtoon/view/".$value['nickname']."'>".$value['title']."<br>[".$value['cartoon']['artists'][0]['penName']."/".round($value['averageScore'],3)."/".$value['cartoon']['genres'][0]['name'].",".$value['cartoon']['genres'][1]['name']."/".strtoupper($value['webtoonWeeks'][0]['weekDay'])."]</a></span><span style='font-family:NanumGothic;margin-top: 8px;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 1;-webkit-box-orient: vertical;word-wrap:break-word; line-height: 1.2em;height: 1.2em;'><a href='http://m.webtoon.daum.net/m/webtoon/viewer/".$value['latestWebtoonEpisode']['id']."'>".$value['latestWebtoonEpisode']['title']."</a></span></li>";
	};
	echo "</div>";

	//var_dump($data_array);

?>
				</div>
			</dd>
		</dl>
	</div>
</body>
</html>