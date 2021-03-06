<?php
require_once('../rss/autoloader.php');

$pages = array(

	"01" => array(
		"title" => "GIGAZINE",
		"rss"   => "http://feed.rssad.jp/rss/gigazine/rss_2.0",
		"body"  => array(
			"start_word" => "",
			"end_word"   => "",
			"end_tag"    => ""
		),
		"getContents" => false
	),
	
	"02" => array(
		"title" => "CNET Japan 最新情報　総合",
		"url"   => "http://feeds.japan.cnet.com/rss/cnet/all.rdf",
		"body"  => array(
			"start_word" => "",
			"end_word"   => "",
			"end_tag"    => ""
		),
		"getContents" => false
	),
	
	"03" => array(
		"title" => "CNET Japan ブログネットワーク",
		"url"   => "http://feeds.japan.cnet.com/rss/cnet/blog.rdf",
		"body"  => array(
			"start_word" => "articleBody",
			"end_word"   => "clearfix disclaimer",
			"end_tag"    => ""
		),
		"getContents" => true
	),
	
	"04" => array(
		"title" => "InfoQ",
		"url"   => "https://www.infoq.com/jp/feed",
		"body"  => array(
			"start_word" => "",
			"end_word"   => "",
			"end_tag"    => ""
		),
		"getContents" => false
	),
	
	"05" => array(
		"title" => "CodeZine:新着一覧",
		"url"   => "http://rss.rssad.jp/rss/codezine/new/20/index.xml",
		"body"  => array(
			"start_word" => "",
			"end_word"   => "",
			"end_tag"    => ""
		),
		"getContents" => true
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
	$feed->enable_cache(false);
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
				"site_name" => $page["title"],
				"getContents" => $page["getContents"]
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
