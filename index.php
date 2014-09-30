<?
header('Content-Type: application/xml; charset=utf-8');
include "simple_html_dom.php";
function conv($str, $from="euc-kr", $to="utf-8"){
  return trim(iconv($from, $to, $str));
}
function generateRSS($item){
  $rss = "<item>\n";
  foreach($item as $k=>$v){
    //$rss .= "<$k>".htmlspecialchars($v, ENT_NOQUOTES)."</$k>\n";
    //$rss .= "<$k>".str_replace(array('&', '<'), array('&#x26;', '&#x3C;'), 
	//htmlspecialchars($v, ENT_NOQUOTES))."</$k>\n";
	if($k == 'img'){
		if(strlen($v) > 0 ){
			$rss .= "<media:content xmlns:media='http://search.yahoo.com/mrss' ";
			$rss .= "url='$v' type='image/jpeg' medium='image'>";
			$rss .= "<media:description>image</media:description></media:content>";
		}
	}
	else{
		$rss .= "<$k>";
		$rss .= str_replace(array('&', '<'), array('&#x26;', '&#x3C;'), 
			htmlspecialchars($v, ENT_NOQUOTES));
		$rss .= "</$k>\n";
	}

    //$rss .= "<$k><![CDATA[".$v."]]></$k>\n";
  }
  $rss .= "</item>\n";
  return $rss;
}
function extractItem($el, $cat){
  $d = array();
  $a = $el->find('dt a', 0);
  $d['title'] = conv($a->plaintext);
  $cat2 = conv($el->parent()->parent()->parent()->find('h4', 0)->plaintext);
  if( strlen($cat2) > 0 )
	  $cat = $cat2;
  $d['category'] = $cat;
  $d['link'] = "http://news.naver.com".$a->href;
  $d['guid'] = "http://news.naver.com".$a->href;
  $pubDate = new DateTime(conv($el->find('span[class=num]', 0)->innertext));
  //$pubDate->setTimezone(new DateTimeZone('Asia/Seoul'));
  $d['pubDate'] = $pubDate->format("D, d M Y H:i:s O");

  $d['author'] = conv($el->find('dt span', 0)->plaintext);
  if(strlen($d['author']) == 0) {
    $d['author'] = conv($el->find('dd span', 0)->plaintext);
  }
  //$d['author'] = preg_replace("/(\s+)/", "_", $d['author'])."@test.com";
  $d['author'] = "ignore@email.com ({$d['author']})";
  $d['description'] = conv($el->find('dd text', 0)->plaintext);
  $img = $el->parent()->find('div a img', 0)->src;
  $d['img'] = $img;
  return $d;
}
function extractCategory($html){
	return conv($html->find('ul.massmedia li a.on text', 0)->plaintext);
}
//$url = "naver.html";
$url =
"http://news.naver.com/main/ranking/popularDay.nhn?rankingType=popular_day&".$_SERVER['QUERY_STRING'];
$html = file_get_html($url);
$title = conv($html->find('title', 0)->plaintext);
$title = preg_replace("/(\s+)/", " ", $title);
$category = extractCategory($html);
$result = array();
foreach($html->find('div[class=ranking_top3] ol li dl') as $el){
  $result[] = extractItem($el, $category);
}
foreach($html->find('div[class=ranking_section] ol li dl') as $el){
  $result[] = extractItem($el, $category);
}
$rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';
$rssfeed .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
$rssfeed .= '<channel>';
$rssfeed .= "<title>$title</title>";
$rssfeed .= '<link>http://news.naver.com</link>';
$rssfeed .= "<description>네이버 랭킹뉴스 rss 버전 by Chaehyun Lee</description>\n";
$rssfeed .= "<language>kor</language>\n";
//$rssfeed .= "<atom:link href=\"".htmlspecialchars($url)."\" rel=\"self\" type=\"application/rss+xml\" />\n";
//$rssfeed .= "<copyright>chaehyun.kr</copyright>\n";

foreach($result as $item){
  $rssfeed .= generateRSS($item);
}

$rssfeed .= "</channel>\n";
$rssfeed .= "</rss>\n";
print $rssfeed;
?>
