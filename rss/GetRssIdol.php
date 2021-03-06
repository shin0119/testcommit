<?php
require_once('autoloader.php');

$pages = array(
	
	"01" => array(
		"title" => "地下帝国-AKB48まとめ",
		"rss"   => "http://chikakb.ldblog.jp/index.rdf",
		"body"  => array(
			"start_word" => "class=\"post\"",
			"end_word"   => "引用元:",
			"end_tag"    => "</div>"
		)
	),
	
	"02" => array(
		"title" => "AKBip!",
		"url"   => "http://blog.livedoor.jp/akbip/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "引用元:",
			"end_tag"    => "</div></div>"
		)
	),
	
	"03" => array(
		"title" => "若草日誌(AKB48研究生まとめブログ)",
		"url"   => "http://akbmatomeatoz.ldblog.jp/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "引用元:",
			"end_tag"    => "</div></div>"
		)
	),
	
	"04" => array(
		"title" => "ぱるる情報局",
		"url"   => "http://akbakb0048.doorblog.jp/index.rdf",
		"body"  => array(
			"start_word" => "article-body",
			"end_word"   => "article-footer",
			"end_tag"    => ""
		)
	),
	
	"05" => array(
		"title" => "AKB48地下速報",
		"url"   => "http://blog.livedoor.jp/akb48rhyh/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "引用元：",
			"end_tag"    => "</div></div>"
		)
	),
	
	"06" => array(
		"title" => "ひたすらAKB",
		"url"   => "http://htsr-akb.doorblog.jp/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "ldblog_related_articles",
			"end_tag"    => ""
		)
	),
	
	"07" => array(
		"title" => "48GまとめJOURNAL",
		"url"   => "http://momokuroz.2chblog.jp/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "article-tags",
			"end_tag"    => ""
		)
	),
	
	"08" => array(
		"title" => "HKTまとめもん【HKT48のまとめ】",
		"url"   => "http://blog.livedoor.jp/hktmatomemon/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "引用元…",
			"end_tag"    => "</div></div>"
		)
	),
	
	"09" => array(
		"title" => "欅坂46まとめ坂",
		"url"   => "http://www.dreamvocalaudition.jp/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "引用元",
			"end_tag"    => "</div></div>"
		)
	),
	
	"10" => array(
		"title" => "ももクロ侍",
		"url"   => "http://momoclozamurai.xxxblog.jp/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "関連リンク",
			"end_tag"    => "</div></div>"
		)
	)
);


$pid = isset($_GET["pid"]) ? $_GET["pid"] : "";
if ($pid != "" && !isset($pages[$pid])) exit();

$data = array();

$url = isset($_GET["url"]) ? $_GET["url"] : "";
if ($url != "")
{
	mb_language("Japanese");
	
	//$contents = @file_get_contents($url);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$contents = curl_exec($ch);
	curl_close($ch);

	$contents = preg_split('/\r|\n|\r\n/', $contents);

	$body_array = array();
	$body_flag = false;
	
	for ($i = 0, $count = count($contents); $i < $count; $i++) {
		$body = mb_convert_encoding(trim($contents[$i]), "utf-8", "auto");
		
		if (!$body_flag && strpos($contents[$i], $pages[$pid]["body"]["start_word"]) !== false) {
			$body_flag = true;
		}
		else if ($body_flag && strpos($contents[$i], $pages[$pid]["body"]["end_word"]) !== false) {
			$body_flag = false;
		}
		
		if ($body_flag && $body != "") {
			array_push($body_array, $body);
		}
	}
	
	$body = implode("", $body_array);
	
	$body = preg_replace('/<\!\-\-((?!\-\->).)+\-\->/', "", $body);
	$body = preg_replace('/<script((?!\/script>).)+\/script>/', "", $body);
	$body = preg_replace('/<iframe((?!\/iframe>).)+\/iframe>/', "", $body);
	$body = preg_replace('/<ins((?!\/ins>).)+\/ins>/', "", $body);
	
	$body = preg_replace('/<span><\/span>/', "", $body);
	$body = preg_replace('/<li><\/li>/', "", $body);
	$body = preg_replace('/<ul><\/ul>/', "", $body);
	$body = preg_replace('/<div><\/div>/', "", $body);
	
	$body = preg_replace('/\sid=\"?[^\"|^>]+\"?/', "", $body);
	$body = preg_replace('/\sclass=\"?[^\"|^>]+\"?/', "", $body);
	$body = preg_replace('/\swidth=\"?[^\"|^>]+\"?/', "", $body);
	$body = preg_replace('/\sheight=\"?[^\"|^>]+\"?/', "", $body);
	$body = preg_replace('/\s>/', ">", $body);

	$body = $body . "\n" . $pages[$pid]["body"]["end_tag"];
	
	$data = array($body);
}
else
{
	$start = isset($_GET["start"]) ? is_numeric($_GET["start"]) ? intval($_GET["start"]) : 1 : 1;
	$length = isset($_GET["length"]) ? is_numeric($_GET["length"]) ? intval($_GET["length"]) : 15 : 15;
	
	$feed = new SimplePie();
	$sort = array();
	
	foreach($pages as $key => $page)
	{
		if ($pid != "" && $pid != $key) continue;
		
		$feed->set_feed_url($page["url"]);
		$feed->init();
		
		foreach ($feed->get_items() as $item)
		{
			$date = $item->get_date("Y/m/d H:i:s");
			
			$image = "";
			if(preg_match('/<img((?!src).)+src=\"([^\"]+).(jpg|png|gif|jpeg)\"/i', $item->get_content(), $matches))
			{
				$image = $matches[2] . "." . $matches[3];
			}
			if ($image == "" &&	preg_match('/<img((?!src).)+src=\"([^\"]+)\.(jpg|png|gif|jpeg)\"/i', $item->get_description(), $matches))
			{
				$image = $matches[2] . "." . $matches[3];
			}
			if ($image == "")
			{
				$thumbnail = $item->get_thumbnail();
				if (isset($thumbnail["url"])) $image = $thumbnail["url"];
			}
			if ($image == "")
			{
				if ($enclosure = $item->get_enclosure())
				{
					$image = $enclosure->get_link();
				}
			}
			
			array_push($data, array(
				"pid" => $key,
				"title" => $item->get_title(),
				"link" => $item->get_permalink(),
				"date" => $date,
				"image" => $image,
				"site_name" => $page["title"]
			));
			
			array_push($sort, $date);
		}
	}
	
	array_multisort($sort, SORT_DESC, $data);
	$data = array_slice($data, $start-1, $length);
}

$json = json_encode($data);

header("Content-Type: application/json; charset=utf-8");
echo $json;

?>
