<?php

require_once('autoloader.php');

$pages = array(
	
	"01" => array(
		"title" => "Yahoo!ニュース・トピックス - 経済",
		"url"   => "https://news.yahoo.co.jp/pickup/economy/rss.xml"
	),
	
	"02" => array(
		"title" => "ウォール・ストリート・ジャーナル",
		"url"   => "http://jp.wsj.com/xml/rss/3_9704.xml"
	),
	
	"03" => array(
		"title" => "経済・マネー - 朝日新聞デジタル",
		"url"   => "http://rss.asahi.com/rss/asahi/business.rdf"
	),
	
	"04" => array(
		"title" => "日テレNEWS24",
		"url"   => "http://feed.rssad.jp/rss/news24/index.rdf"
	),
	
	"05" => array(
		"title" => "経済 - 注目　ニュース：@nifty",
		"url"   => "https://news.nifty.com/rss/topics_economy.xml"
	),
	
	"06" => array(
		"title" => "経済ニュース速報 - SankeiBiz（サンケイビズ）",
		"url"   => "http://rss.rssad.jp/rss/sankeibiz/flash"
	)
);

$pid = isset($_GET["pid"]) ? $_GET["pid"] : "";
if ($pid != "" && !isset($pages[$pid])) exit();

$start = isset($_GET["start"]) ? is_numeric($_GET["start"]) ? intval($_GET["start"]) : 1 : 1;
$length = isset($_GET["length"]) ? is_numeric($_GET["length"]) ? intval($_GET["length"]) : 15 : 15;

$feed = new SimplePie();
$data = array();
$sort = array();

foreach($pages as $key => $page) {
	if ($pid != "" && $pid != $key) continue;
	$feed->set_feed_url($page["url"]);
	$feed->init();
	
	foreach ($feed->get_items() as $item) {
		$date = $item->get_date("Y/m/d H:i:s");
		
		$image = "";
		if(preg_match('/src="(.*?)\.(jpg|gif|png)"/i', $item->get_content(), $matches)) {
			$image = $matches[1] . "." . $matches[2];
		}
		if ($image == "" &&	preg_match('/src="(.*?)\.(jpg|gif|png)(\?.+)*"/i', $item->get_description(), $matches)) {
			$image = $matches[1] . "." . $matches[2];
		}
		if ($image == "") {
			$thumbnail = $item->get_thumbnail();
			if (isset($thumbnail['url'])) $image = $thumbnail['url'];
		}
		if ($image == "") {
			if ($enclosure = $item->get_enclosure()) {
				$image = $enclosure->get_link();
			}
		}
		
		array_push($data, array(
			"title" => $item->get_title(),
			"link" => $item->get_permalink(),
			"date" => $date,
			"image" => $image,
			"category" => $page["title"]
		));
		
		array_push($sort, $date);
	}
}

array_multisort($sort, SORT_DESC, $data);
$data = array_slice($data, $start-1, $length);

$json = json_encode($data);

header("Content-Type: application/json; charset=utf-8");
echo $json;
?>
