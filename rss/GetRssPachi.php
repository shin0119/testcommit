<?php
require_once('autoloader.php');

$pages = array(

	"01" => array(
		"title" => "パチパチマニアックス",
		"rss"   => "http://slot.2chblog.jp/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "id=\"ad2\"",
			"end_tag"    => "</div>"
		)
	),
	
	"02" => array(
		"title" => "スロ板-RUSH",
		"url"   => "http://fiveslot777.com/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "id=\"0be24c837874564df41176f053532518\"",
			"end_tag"    => "</div></div>"
		)
	),
	
	"03" => array(
		"title" => "パチンコ・パチスロ｜2chflix",
		"url"   => "http://2chflix.com/gambling/feed",
		"body"  => array(
			"start_word" => "the-content",
			"end_word"   => "id=\"sns-group\"",
			"end_tag"    => "</footer>"
		)
	),
	
	"04" => array(
		"title" => "パチ速：パチンコ・パチスロまとめ",
		"url"   => "http://blog.livedoor.jp/he0704/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "article-tags",
			"end_tag"    => "</div>"
		)
	),
	
	"05" => array(
		"title" => "コジツケ君がゆく",
		"url"   => "http://rssblog.ameba.jp/kojitukekun/rss20.xml",
		"body"  => array(
			"start_word" => "skinArticleBody2",
			"end_word"   => "articleBtnArea",
			"end_tag"    => "</div></div></div>"
		)
	),
	
	"06" => array(
		"title" => "スロパチランド",
		"url"   => "http://spland3.net/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "table　cellspacing=\"0\"",
			"end_tag"    => ""
		)
	),

	"07" => array(
		"title" => "パチンカス",
		"url"   => "http://pachinkas.net/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "clearfix info_social",
			"end_tag"    => ""
		)
	),

	"08" => array(
		"title" => "パチンコ・パチスロ.com",
		"url"   => "http://pachinkopachisro.com/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "<div id=\"ldblog_related_articles",
			"end_tag"    => ""
		)
	)
);


$pid = isset($_GET["pid"]) ? $_GET["pid"] : "";
if ($pid != "" && !isset($pages[$pid])) exit();

$data = array();

$rssCntFlag = isset($_GET["rsscnt"]) ? true : false;

$noget = isset($_GET["noget"]) ? $_GET["noget"] : "";

$url = isset($_GET["url"]) ? $_GET["url"] : "";
if ($url != "")
{
	mb_language("Japanese");
	
	//$contents = @file_get_contents($url);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	$contents = curl_exec($ch);
	$curl_info = curl_getinfo($ch);
	curl_close($ch);

	if ($curl_info["http_code"] == 200) {
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
	else {
		$data = array();
	}
}
elseif ($rssCntFlag)
{
	foreach($pages as $key => $page)
	{
		array_push($data, [$key, $page['title']]);
	}
}
else
{
	$start = isset($_GET["start"]) ? is_numeric($_GET["start"]) ? intval($_GET["start"]) : 1 : 1;
	$length = isset($_GET["length"]) ? is_numeric($_GET["length"]) ? intval($_GET["length"]) : 15 : 15;
	
	$feed = new SimplePie();
	$sort = array();
	
	foreach($pages as $key => $page)
	{
		$nogetFlag = false;
		if($noget){
			foreach ($noget as $nogetKey => $nogetVal)
			{
				if ($nogetVal == $key) $nogetFlag = true;
			}
			if($nogetFlag) continue;
		}
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
