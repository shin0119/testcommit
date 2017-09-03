<?php
require_once('autoloader.php');

$pages = array(

	"01" => array(
		"title" => "FX2ｃｈまとめブログ",
		"rss"   => "http://fx2chmatome.blog.jp/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "SOURCE : ",
			"end_tag"    => "</div></div>"
		)
	),
	
	"02" => array(
		"title" => "GI　今日の目線",
		"url"   => "http://gi24blog.com/index.rdf",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "FX(為替)を中心に",
			"end_tag"    => "</div></div></div>"
		)
	),
	
	"03" => array(
		"title" => "野村雅道のID為替研究所 (Day)",
		"url"   => "http://www.gaitame.com/blog/nomura/rss.xml",
		"body"  => array(
			"start_word" => "articleBox",
			"end_word"   => "articleFooter",
			"end_tag"    => ""
		)
	),
	
	"04" => array(
		"title" => "松本鉄郎のポイント・アンド・フィギュアによる実戦相場予測",
		"url"   => "http://www.gaitame.com/blog/matsumoto/rss.xml",
		"body"  => array(
			"start_word" => "articleBox",
			"end_word"   => "articleFooter",
			"end_tag"    => ""
		)
	),
	
	"05" => array(
		"title" => "野村雅道のID為替 (レポート)",
		"url"   => "http://www.gaitame.com/blog/nomura_report/rss.xml",
		"body"  => array(
			"start_word" => "articleBox",
			"end_word"   => "articleFooter",
			"end_tag"    => ""
		)
	)/*,
	
	"06" => array(
		"title" => "経済・マネー - 朝日新聞デジタル",
		"url"   => "http://rss.asahi.com/rss/asahi/business.rdf",
		"body"  => array(
			"start_word" => "ArticleText",
			"end_word"   => "ArticleText END",
			"end_tag"    => ""
		)
	),
	
	"07" => array(
		"title" => "NHK　経済",
		"url"   => "http://www3.nhk.or.jp/rss/news/cat5.xml",
		"body"  => array(
			"start_word" => "detail-no-js",
			"end_word"   => "</section>",
			"end_tag"    => "</section>"
		)
	),
	
	"08" => array(
		"title" => "ロイター",
		"url"   => "http://www.newsweekjapan.jp/headlines/rss.xml",
		"body"  => array(
			"start_word" => "panelNoShadow",
			"end_word"   => "reuters",
			"end_tag"    => "</div></div></div></div>"
		)
	),
	
	"09" => array(
		"title" => "From the Newsroom",
		"url"   => "http://www.newsweekjapan.jp/newsroom/atom.xml",
		"body"  => array(
			"start_word" => "entryDetailBodyCopy",
			"end_word"   => "＊次ページから、",
			"end_tag"    => "</div>"
		)
	),
	
	"10" => array(
		"title" => "東洋経済",
		"url"   => "http://toyokeizai.net/list/feed/rss",
		"body"  => array(
			"start_word" => "article-body-inner",
			"end_word"   => "div-gpt-ad-inread",
			"end_tag"    => ""
		)
	),
	
	"11" => array(
		"title" => "現代ビジネス",
		"url"   => "http://feed.ismedia.jp/rss/gendai/all.rdf",
		"body"  => array(
			"start_word" => "articleContents",
			"end_word"   => "nextpageTitle",
			"end_tag"    => "</div>"
		)
	)*/
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
