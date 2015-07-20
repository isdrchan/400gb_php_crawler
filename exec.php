<?php
error_reporting(0);
set_time_limit(0);
run(80000980, 80000990);

function get_data($true_url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $true_url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	//内容不输出到浏览器
	$content = curl_exec($ch);
	curl_close($ch);
	return $content;
}

function preg_data($content) {
	//非正常页面判断
	$wrong_page_str = '<h4 class="blue">系统提示</h4>';
	if(strstr($content, $wrong_page_str)) 
		return false;

	//正则匹配抓取关键词
	$search_h1 = '/<h1>(.*?)<small>/si';
	$search_small = '/<small>(.*?)<\/small>/si';
	$search_path = '/<a target=\"_blank\" href=\"\/u(.*?)\".*?>(.*?)<\/a>/i';
	preg_match_all($search_h1 ,$content, $res1);
	preg_match_all($search_small ,$content, $res2);
	preg_match_all($search_path ,$content, $res3);
	$file_info['name'] = $res1[1][0];
	$file_info['size'] = trim(count($res2[1]) > 1 ? $res2[1][1] : $res2[1][0]);	//去前后空格、过滤“文件名隐藏”
	$file_info['path'] = $res3[2];
	return $file_info;
}

function run($start, $end) {
	$url = "http://www.400gb.com/file/";
	for($num = $start; $num <= $end; $num++) {
		$content = get_data($url.$num);
		$file_info['url'] = $url.$num;
		$temp_array = preg_data($content);
		if(!$temp_array) continue;
		$file_info = array_merge($file_info, $temp_array);
		$file_info_json = stripslashes(json_encode($file_info, JSON_UNESCAPED_UNICODE));	//使转换的json字符串并去掉url的斜杠
		// echo $file_info_json."\n";
		file_put_contents($start."-".$end.".txt", $file_info_json."\r\n", FILE_APPEND);
	}
	echo "Done!";
}
