<?php

$app = array(
	array(
		"title" => "ワンコのFX入門",
		"text" => "FXを始めてみよう。FXがやさしく学べるアプリ",
		"url"  => array(
			"ios" => "",
			"android"   => "https://play.google.com/store/apps/details?id=wanko.fx.nyumon&hl=ja"
		)
	),/*,

	array(
		"title" => "経済ニュース｜FXに役立つ経済ニュース",
		"text" => "FXの定番アプリ。FXに必要な経済ニュースを一覧化しました。FX初心者にもわかりやすくまとめています。FXにお役立てください。",
		"url"  => array(
			"ios" => "",
			"android"   => "https://play.google.com/store/apps/details?id=com.fx.news.info&hl=ja"
		)
	),*/
	
	array(
		"title" => "FX入門アプリ",
		"text" => "FXまだやってないの？FXがやさしく学べるアプリ登場",
		"url"  => array(
			"ios" => "https://itunes.apple.com/us/app/fx%E5%85%A5%E9%96%80-%E3%83%9E%E3%83%AA%E5%A7%89%E3%81%8C%E6%95%99%E3%81%88%E3%82%8Bfx/id1241224430?mt=8",
			"android"   => "https://play.google.com/store/apps/details?id=wanko.fx.nyumon&hl=ja"
		)
	)
);

$num = rand(0, count($app)) -1;
$json = json_encode($app[$num]);

header("Content-Type: application/json; charset=utf-8");
echo $json;

?>
