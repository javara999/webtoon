<?php
	include('../../lib/header.php');
	$epiParam = str_replace("{toondtlid}",$_GET["ws_id"],$viewParam);
	$epiParam = str_replace("{toonid}",$_GET["wr_id"],$epiParam);

	$get_images = array();
	$url = $siteUrl.$viewUrl."?".$epiParam; //주소셋팅
	if ( $config["cf_redirect"] != null && $config["cf_redirect"] == "Y" ) {
		$url = $config_add1["cf_redirect"]."?try_count=".$config["try_count"]."&cf_cookie=".urlencode($config["cf_cookie"])."&cf_useragent=".urlencode($config["cf_useragent"])."&target_url=".urlencode($url);
	}
	echo "<script type='text/javascript'>console.log('".$url."');</script>";
	echo "<script type='text/javascript'>console.log('THUMB=".$_SESSION["THUMB"]."');</script>";
	$ch = curl_init(); //curl 로딩
	curl_setopt($ch, CURLOPT_URL,$url); //curl에 url 셋팅
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 이 셋팅은 1로 고정하는 것이 정신건강에 좋음
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Safari/537.36');
	curl_setopt($ch, CURLOPT_TIMEOUT,3000);
	$result = curl_exec($ch); // curl 실행 및 결과값 저장
	for($html_c = 0; $html_c < (int)$config["try_count"]; $html_c++){
		if(strlen($result) > 10000){
			break;
		} else {
			$result = curl_exec($ch);
		}
	}
	curl_close ($ch); // curl 종료

	$html_arr = explode("<title>", $result);
	$get_html_contents = str_get_html($result);

	foreach($get_html_contents->find('span') as $e){
		if ( $e->class == "viewer-header__title" ) {
			$epititle = trim(strip_tags($e));
		}
	}

	if ( $isLogin ) {
		$isAlreadyView = "SELECT MBR_NO, SITE_ID, TOON_ID, REGDTIME, UPTDTIME FROM USER_VIEW ";
		$isAlreadyView = $isAlreadyView." WHERE MBR_NO = '".$MBR_NO."' AND SITE_ID = '".$siteId."' AND TOON_ID = '".$_GET['wr_id']."' AND USE_YN='Y' ";
		$isAlreadyView = $isAlreadyView." LIMIT 1;";
		$webtoonView = $webtoonDB->query($isAlreadyView);
		while($row = $webtoonView->fetchArray(SQLITE3_ASSOC)){         
			$viewDate = $row["UPTDTIME"];
		}
		if ( strlen($viewDate) == 0 ) {
			$sql_view = "INSERT INTO 'USER_VIEW' ('MBR_NO', 'SITE_ID', 'TOON_ID', 'TOON_URL', 'TOON_PARAM', 'TOON_THUMBNAIL', 'TOON_TITLE', 'USE_YN', 'REGDTIME', 'UPTDTIME')";
			$sql_view = $sql_view." VALUES ('".$MBR_NO."','".$siteId."','".$_GET['wr_id']."','".$siteUrl.$listUrl."','".str_replace("{toonid}",$_GET["wr_id"],$listParam)."','".$_SESSION["THUMB"]."','".$_GET["title"]."', 'Y', '".$thisTime."', '".$thisTime."'); ";
			$webtoonDB->exec($sql_view);
		} else {
			$sql_view = "UPDATE 'USER_VIEW' SET UPTDTIME = '".$thisTime."' ";
			if ( $_SESSION["THUMB"]!=null && strlen($_SESSION["THUMB"])>0 ) {
				$sql_view = $sql_view.", TOON_THUMBNAIL = '".$_SESSION["THUMB"]."' ";
			}
			$sql_view = $sql_view."WHERE MBR_NO = '".$MBR_NO."' AND SITE_ID = '".$siteId."' AND TOON_ID = '".$_GET['wr_id']."' AND USE_YN = 'Y';";
			$webtoonDB->exec($sql_view);
		}
		$viewDate = "";
		$isAlreadyView = "SELECT MBR_NO, SITE_ID, TOON_ID, VIEW_ID, REGDTIME, UPTDTIME FROM USER_VIEW_DTL ";
		$isAlreadyView = $isAlreadyView." WHERE MBR_NO = '".$MBR_NO."' AND SITE_ID = '".$siteId."' AND TOON_ID = '".$_GET['wr_id']."' AND VIEW_ID = '".$_GET['ws_id']."' AND USE_YN='Y' ";
		$isAlreadyView = $isAlreadyView." LIMIT 1;";
		$webtoonView = $webtoonDB->query($isAlreadyView);
		while($row = $webtoonView->fetchArray(SQLITE3_ASSOC)){         
			$viewDate = $row["UPTDTIME"];
		}
		if ( strlen($viewDate) == 0 ) {
			$sql_view = "INSERT INTO 'USER_VIEW_DTL' ('MBR_NO', 'SITE_ID', 'TOON_ID', 'VIEW_ID', 'VIEW_URL', 'VIEW_PARAM', 'VIEW_TITLE', 'USE_YN', 'REGDTIME', 'UPTDTIME')";
			$sql_view = $sql_view." VALUES ('".$MBR_NO."','".$siteId."','".$_GET['wr_id']."','".$_GET['ws_id']."', '".$viewUrl."?".$epiParam."','','".$epititle."','Y', '".$thisTime."', '".$thisTime."'); ";
			$webtoonDB->exec($sql_view);
		} else {
			$sql_view = "UPDATE 'USER_VIEW_DTL' SET UPTDTIME = '".$thisTime."' ";
			$sql_view = $sql_view."WHERE MBR_NO = '".$MBR_NO."' AND SITE_ID = '".$siteId."' AND TOON_ID = '".$_GET['wr_id']."' AND VIEW_ID = '".$_GET['ws_id']."' AND USE_YN = 'Y';";
			$webtoonDB->exec($sql_view);
		}
	}

	foreach($get_html_contents->find('ul.inline-list') as $e){
		$f = str_get_html($e->innertext);
		$idx=0;
		foreach($f->find('button') as $g){
			if ( $idx==0 ) {
				if ( $g->onclick != null ) {
					$prevurl = $g->onclick;
					$prevurl = str_replace("location.href='./board.php?bo_table=toons&amp;wr_id=","",$prevurl);
					$prevpos = explode("&",$prevurl);
					$prevwsis = $prevpos[0];
				}
			}
			if ( $idx==1 ) {
				if ( $g->onclick != null ) {
					$nexturl = $g->onclick;
					$nexturl = str_replace("location.href='./board.php?bo_table=toons&amp;wr_id=","",$nexturl);
					$nextpos = explode("&",$nexturl);
					$nextwsis = $nextpos[0];
				}
			}
			$idx++;
		}
	}


	$selector_arr = explode("var img_list = [", $result);
	$html_arr = explode("];", $selector_arr[1]);
	$img_html = $html_arr[0];
	$img_html = str_replace('"','',$img_html);

	$imgurl = explode(",",$img_html);
?>
<script type="text/javascript">
		function view(mode) {
			if ( mode=="pageView") {
				document.getElementById("closeDiv1").style.display = "";
				document.getElementById("closeDiv2").style.display = "";
				document.getElementById("prevDiv").style.display = "";
				document.getElementById("nextDiv").style.display = "";
				document.getElementById("listview").style.display = "";
				document.getElementById("container").style.display = "none";
			} else {
				document.getElementById("closeDiv1").style.display = "none";
				document.getElementById("closeDiv2").style.display = "none";
				document.getElementById("prevDiv").style.display = "none";
				document.getElementById("nextDiv").style.display = "none";
				document.getElementById("listview").style.display = "none";
				document.getElementById("container").style.display = "";
			}
		}

		function prev() {
			if ( imageIndex > 0 ) {
				document.getElementById("imgview").src=img_list[imageIndex-1];
				imageIndex = imageIndex-1;
			} else {
				document.getElementById("imgview").src=img_list[0];
				imageIndex = 0;
			}
		}
		function next() {
			if ( imageIndex+1 >= imageSize ) {
				document.getElementById("imgview").src=img_list[imageSize-1];
				imageIndex = imageSize-1;
			} else {
				document.getElementById("imgview").src=img_list[imageIndex+1];
				imageIndex = imageIndex+1;
			}
		}
</script>

<div id="closeDiv1" style="display:none;top:5%;right:10px;position:absolute;" onClick="view('listView');"><img src="../lib/img/close.png" width="30" height="30"></div>
<div id="prevDiv" style="display:none;top:10%;left:10px;height:80%;width:45%;position:absolute;" valign="middle" onClick="prev();"></div>
<div id="nextDiv" style="display:none;top:10%;right:10px;height:80%;width:45%;position:absolute;" valign="middle" onClick="next();"></div>
<div id="closeDiv2" style="display:none;top:90%;left:10px;height:10%;width:95%;position:absolute;" onClick="view('listView');"></div>
<table id="listview" style="display:none;line-height:1.5;border-color:#ffffff;height:100%;" border=1 width="100%" cellspacing=0 cellpadding=0>
<tr style='background-color:#f8f8f8'>
	<td colspan="5" style='width:100%;height:100%;font-size:16px;color:#8000ff;' align=center valign=middle>
		<img id='imgview' src='' style='max-height:100%;max-width:100%;'>
		<script type="text/javascript">
			var imageIndex = 0;
<?php
	$imgidx = 0;
	echo "		var imageSize = ".sizeof($imgurl).";\n";
	echo "		var img_list = new Array(";
	foreach($imgurl as $images){
		if ( substr($images, 0,16) == "https://manatoki" && ( substr($images, 19,3) == "com" || substr($images, 19,3) == "net" )) {
			$images = str_replace(substr($images, 0,23), $siteUrl, $images);
		}
		if ( substr($images, 0,15) == "https://newtoki"  && ( substr($images, 18,3) == "com" || substr($images, 18,3) == "net" )) {
			$images = str_replace(substr($images, 0,22), $siteUrl, $images);
		}
		if ( sizeof($imgurl) == $imgidx+1 ) {
			echo '"'.$images.'");';
		} else echo '"'.$images.'", ';
		$imgidx++;
	}
?>

		document.getElementById("imgview").src=img_list[0];

		function prev() {
			if ( imageIndex > 0 ) {
				document.getElementById("imgview").src=img_list[imageIndex-1];
				imageIndex = imageIndex-1;
			} else {
				document.getElementById("imgview").src=img_list[0];
				imageIndex = 0;
			}
		}
		function next() {
			if ( imageIndex+1 >= imageSize ) {
				document.getElementById("imgview").src=img_list[imageSize-1];
				imageIndex = imageSize-1;
			} else {
				document.getElementById("imgview").src=img_list[imageIndex+1];
				imageIndex = imageIndex+1;
			}
		}
		</script>
	</td>
</tr>
</table>

<div id='container' style="display:">
	<div class='item'>
		<dl>
			<dt><?php echo "<a href='list.php?wr_id=".$_GET['wr_id']."&title=".urlencode($title)."'>".$epititle."</a> <a href='".$target_episode."'><img src='logo.png' height='25px'></a>"; ?></dt>
			<dd>
				<div class='group' style='padding:0px;'>
					<table style="line-height:1.5;border-color:#ffffff;" border=1 width="100%" cellspacing=0 cellpadding=0>
					<tr style='background-color:#f8f8f8'>
						<td colspan="5" style='width:100%;height:10px;font-size:16px;color:#8000ff;' align=center valign=middle></td>
					</tr>
					<tr style='background-color:#f8f8f8'>
						<td style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
						<?php
							if( $prevwsis != null && strlen($prevwsis) > 0 ){
									echo "<a href='view.php?title=".urlencode($title)."&wr_id=".$_GET['wr_id']."&ws_id=".$prevwsis."'>◀</a>";
							} else {
								echo "◁";
							}
						?>
						</td>
						<td style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
						<?php
							if( $nextwsis != null && strlen($nextwsis) > 0 ){
									echo "<a href='view.php?title=".urlencode($title)."&wr_id=".$_GET['wr_id']."&ws_id=".$nextwsis."'>▶</a>";
							} else {
								echo "▷";
							}
						?>
						</td>
						<td style='font-size:16px;color:#8000ff;' align=center valign=middle onClick="view('pageView');">&nbsp;</td>
						<td style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
						<?php
							if( $prevwsis != null && strlen($prevwsis) > 0 ){
									echo "<a href='view.php?title=".urlencode($title)."&wr_id=".$_GET['wr_id']."&ws_id=".$prevwsis."'>◀</a>";
							} else {
								echo "◁";
							}
						?>
						</td>
						<td style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
						<?php
							if( $nextwsis != null && strlen($nextwsis) > 0 ){
									echo "<a href='view.php?title=".urlencode($title)."&wr_id=".$_GET['wr_id']."&ws_id=".$nextwsis."'>▶</a>";
							} else {
								echo "▷";
							}
						?>
						</td>
					</tr>
					<tr style='background-color:#f8f8f8'>
						<td colspan="5" style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
<?php

	foreach($imgurl as $images){
		if ( substr($images, 0,16) == "https://manatoki" ) {
			$images = str_replace(substr($images, 0,16).substr($images, 16,2).".net/", $siteUrl, $images);
		}
		if ( substr($images, 0,15) == "https://newtoki" ) {
			$images = str_replace(substr($images, 0,15).substr($images, 15,2).".com/", $siteUrl, $images);
		}
		echo "<img src='".$images."' width='100%'><br>";
	}
?>
					<tr style='background-color:#f8f8f8'>
						<td style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
						<?php
							if( $prevwsis != null && strlen($prevwsis) > 0 ){
									echo "<a href='view.php?title=".urlencode($title)."&wr_id=".$_GET['wr_id']."&ws_id=".$prevwsis."'>◀</a>";
							} else {
								echo "◁";
							}
						?>
						</td>
						<td style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
						<?php
							if( $nextwsis != null && strlen($nextwsis) > 0 ){
									echo "<a href='view.php?title=".urlencode($title)."&wr_id=".$_GET['wr_id']."&ws_id=".$nextwsis."'>▶</a>";
							} else {
								echo "▷";
							}
						?>
						</td>
						<td style='font-size:16px;color:#8000ff;' align=center valign=middle onClick="view('pageView');">&nbsp;</td>
						<td style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
						<?php
							if( $prevwsis != null && strlen($prevwsis) > 0 ){
									echo "<a href='view.php?title=".urlencode($title)."&wr_id=".$_GET['wr_id']."&ws_id=".$prevwsis."'>◀</a>";
							} else {
								echo "◁";
							}
						?>
						</td>
						<td style='width:10%;font-size:16px;color:#8000ff;' align=center valign=middle>
						<?php
							if( $nextwsis != null && strlen($nextwsis) > 0 ){
									echo "<a href='view.php?title=".urlencode($title)."&wr_id=".$_GET['wr_id']."&ws_id=".$nextwsis."'>▶</a>";
							} else {
								echo "▷";
							}
						?>
						</td>
					</tr>
					</table>
				</div>
			</dd>
		</dl>
	</div>
</div>
</body>
</html>
