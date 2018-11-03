<?php
error_reporting(0);

function save($filename, $content)
{
	$save = fopen($filename, "a+");
	fputs($save, "$content\r\n");
	fclose($save);
}

function getstr($string, $start, $end)
{
	$str = explode($start, $string);
	$str = explode($end, $str[1]);
	return $str[0];
}

function csrf()
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
		'accept-language: en-US,en;q=0.9,id;q=0.8',
		'cache-control: max-age=0',
		'upgrade-insecure-requests: 1',
		'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
	));
	$res = curl_exec($ch);

	// echo $res;

	$header = substr($res, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
	$body = substr($res, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
	curl_close($ch);
	$cookie = getstr($header, "csrftoken=", ";");
	return ["header" => $header, "csrftoken" => $cookie, "rur" => getstr($header, "rur=", ";") , "mid" => getstr($header, "mid=", ";") ];
}

function login($username, $password)
{
	$csrf = csrf() ["csrftoken"];

	// echo $csrf;

	$data = "username=$username&password=$password&queryParams=%7B%22source%22%3A%22auth_switcher%22%7D";
	$head = explode("\n", 'Host: www.instagram.com
Connection: keep-alive
Origin: https://www.instagram.com
X-Instagram-AJAX: 2c7198c5e9dc
Content-Type: application/x-www-form-urlencoded
Accept: */*
X-Requested-With: XMLHttpRequest
User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36
X-CSRFToken: ' . $csrf . '
Referer: https://www.instagram.com/accounts/login/?source=auth_switcher
Accept-Language: en-US,en;q=0.9
Cookie: mid=W91LuAAEAAHrpc7TqqSBt4C-rzH8; rur=ATN; mcd=3; csrftoken=' . $csrf);
	$c = curl_init("https://www.instagram.com/accounts/login/ajax/");
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($c, CURLOPT_POSTFIELDS, $data);
	curl_setopt($c, CURLOPT_POST, true);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_HEADER, true);
	curl_setopt($c, CURLOPT_HTTPHEADER, $head);
	curl_setopt($c, CURLOPT_COOKIEJAR, "cookiesig.txt");
	curl_setopt($c, CURLOPT_COOKIEFILE, "cookiesig.txt");
	$response = curl_exec($c);
	$httpcode = curl_getinfo($c);
	if (!$httpcode) return false;
	else
	{
		$header = substr($response, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
		$body = substr($response, curl_getinfo($c, CURLINFO_HEADER_SIZE));
	}

	echo $body;
}

function timeline()
{
	$csrf = csrf() ["csrftoken"];

	// echo $csrf;

	$head = explode("\n", 'Host: www.instagram.com
Connection: keep-alive
Origin: https://www.instagram.com
X-Instagram-AJAX: 2c7198c5e9dc
Content-Type: application/x-www-form-urlencoded
Accept: */*
X-Requested-With: XMLHttpRequest
User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36
X-CSRFToken: ' . $csrf . '
Referer: https://www.instagram.com/accounts/login/?source=auth_switcher
Accept-Language: en-US,en;q=0.9
Cookie: mid=W91LuAAEAAHrpc7TqqSBt4C-rzH8; rur=ATN; mcd=3; csrftoken=' . $csrf);
	$c = curl_init("https://www.instagram.com/graphql/query/?query_hash=c409f8bda63382c86db99f2a2ea4a9b2&variables=%7B%7D");
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_HEADER, true);
	curl_setopt($c, CURLOPT_VERBOSE, false);
	curl_setopt($c, CURLOPT_HTTPHEADER, $head);
	curl_setopt($c, CURLOPT_COOKIEJAR, "cookiesig.txt");
	curl_setopt($c, CURLOPT_COOKIEFILE, "cookiesig.txt");
	$response = curl_exec($c);
	$httpcode = curl_getinfo($c);
	if (!$httpcode) return false;
	else
	{
		$header = substr($response, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
		$body = substr($response, curl_getinfo($c, CURLINFO_HEADER_SIZE));
	}

	$json = json_decode($body, true);
	$node = $json['data']['user']['edge_web_feed_timeline']['edges'];
	for ($i = 0; $i < count($node); $i++)
	{
		if ($i == 1)
		{
			continue;
		}

		$owner[] = $node[$i]['node']['owner']['username'];
		$mid[] = $node[$i]['node']['id'];
	}

	return array(
		"owner" => $owner,
		"mid" => $mid
	);
}

function like($id)
{
	$csrf = csrf() ["csrftoken"];

	// echo $csrf;

	$data = "";
	$head = explode("\n", 'Host: www.instagram.com
Connection: keep-alive
Origin: https://www.instagram.com
X-Instagram-AJAX: 2c7198c5e9dc
Content-Type: application/x-www-form-urlencoded
Accept: */*
X-Requested-With: XMLHttpRequest
User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36
X-CSRFToken: ' . $csrf . '
Referer: https://www.instagram.com/accounts/login/?source=auth_switcher
Accept-Language: en-US,en;q=0.9
Cookie: mid=W91LuAAEAAHrpc7TqqSBt4C-rzH8; rur=ATN; mcd=3; csrftoken=' . $csrf);
	$c = curl_init("https://www.instagram.com/web/likes/$id/like/");
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($c, CURLOPT_POSTFIELDS, $data);
	curl_setopt($c, CURLOPT_POST, true);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_HEADER, true);
	curl_setopt($c, CURLOPT_VERBOSE, false);
	curl_setopt($c, CURLOPT_HTTPHEADER, $head);
	curl_setopt($c, CURLOPT_COOKIEJAR, "cookiesig.txt");
	curl_setopt($c, CURLOPT_COOKIEFILE, "cookiesig.txt");
	$response = curl_exec($c);
	$httpcode = curl_getinfo($c);
	if (!$httpcode) return false;
	else
	{
		$header = substr($response, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
		$body = substr($response, curl_getinfo($c, CURLINFO_HEADER_SIZE));
	}

	// echo $body;

	if (preg_match('/{"status": "ok"}/', $body))
	{
		return $id . "|Liked!";
	}
	else
	{
		return "Error";
	}
}

if ($_GET['mode'] == "login")
{
	echo login($_GET['user'], $_GET['pass']);
}
elseif ($_GET['mode'] == "like")
{
	$timeline = timeline();
	for ($i = 0; $i < count($timeline['mid']); $i++)
	{
		$id = $timeline['mid'][$i];
		if (preg_match("/" . $id . "/", file_get_contents("logliked.txt")))
		{
			echo $id . "|Already Liked" . "\n";
		}
		else
		{
			$like = like($timeline['mid'][$i]);
			echo $timeline['owner'][$i] . "|" . $like . "\n";
			if ($like != "Error")
			{
				save("logliked.txt", $timeline['mid'][$i]);
			}
		}
	}
}
?>
