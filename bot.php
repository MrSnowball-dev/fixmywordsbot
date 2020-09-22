<?php
ini_set('display_errors', 1);
include 'config.php';
header('Content-Type: text/html; charset=utf-8');

$api = 'https://api.telegram.org/bot'.$tg_bot_token;

$input = file_get_contents('php://input');
$output = json_decode($input, TRUE); //сюда приходят все запросы по вебхукам

//соединение с БД
// $db = mysqli_connect($db_host, $db_username, $db_pass, $db_schema);
// mysqli_set_charset($db, 'utf8mb4');
// mysqli_query($db, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
// if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
// 	else echo "MySQL connect successful.\n";

// if ($check = mysqli_query($db, 'select * from main')) {
// 	$count = mysqli_num_rows($check);
// 	echo "There is $count records in DB.\n\n";
// 	mysqli_free_result($check);
// }

//телеграмные события
$chat_id = isset($output['message']['chat']['id']) ? $output['message']['chat']['id'] : 'chat_id_empty'; //отделяем id чата, откуда идет обращение к боту
$chat_type = isset($output['message']['chat']['type']) ? $output['message']['chat']['type'] : 'no_chat_type';
$message = isset($output['message']['text']) ? $output['message']['text'] : 'message_text_empty'; //сам текст сообщения
$user = isset($output['message']['from']['username']) ? $output['message']['from']['username'] : 'origin_user_empty';
$user_id = isset($output['message']['from']['id']) ? $output['message']['from']['id'] : 'origin_user_id_empty';
$message_id = isset($output['message']['message_id']) ? $output['message']['message_id'] : 'message_id_empty';
$inline = isset($output['inline_query']) ? $output['inline_query'] : 'inline_query_empty';
$query_id = isset($inline['id']) ? $inline['id'] : 'inline_query_id_empty';
$query = isset($inline['query']) ? $inline['query'] : 'inline_query_empty';
$reply = isset($output['message']['reply_to_message']) ? $output['message']['reply_to_message'] : 'reply_empty';
$reply_message_id = isset($reply['message_id']) ? $reply['message_id'] : 'reply_message_id_empty';
$reply_message_text = isset($reply['text']) ? $reply['text'] : 'reply_message_empty';
// $new_user = isset($output['message']['new_chat_members']) ? $output['message']['new_chat_members'] : 'new_user_empty';

$translated_chars_array = array(
	"q" => "й", "Q" => "Й",
	"w" => "ц", "W" => "Ц",
	"e" => "у", "E" => "У",
	"r" => "к", "R" => "К",
	"t" => "е", "T" => "Е",
	"y" => "н", "Y" => "Н",
	"u" => "г", "U" => "Г",
	"i" => "ш", "I" => "Ш",
	"o" => "щ", "O" => "Щ",
	"p" => "з", "P" => "З",
	"[" => "х", "{" => "Х",
	"]" => "ъ", "}" => "Ъ",
	"a" => "ф", "A" => "Ф",
	"s" => "ы", "S" => "Ы",
	"d" => "в", "D" => "В",
	"f" => "а", "F" => "А",
	"g" => "п", "G" => "П",
	"h" => "р", "H" => "Р",
	"j" => "о", "J" => "О",
	"k" => "л", "K" => "Л",
	"l" => "д", "L" => "Д",
	";" => "ж", ":" => "Ж",
	"'" => "э", "\"" => "Э",
	"z" => "я", "Z" => "Я",
	"x" => "ч", "X" => "Ч",
	"c" => "с", "C" => "С",
	"v" => "м", "V" => "М",
	"b" => "и", "B" => "И",
	"n" => "т", "N" => "Т",
	"m" => "ь", "M" => "Ь",
	"," => "б", "<" => "Б",
	"." => "ю", ">" => "Ю",
	"/" => ".", 
	"?" => ",", 
	"`" => "ё", "~" => "Ё",
	"&" => "?",
	"@" => "\"",
	"^" => ":",
	"$" => ";",
	"#" => "№"
);

$cyrillic_alphabet = [
  'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
];

$alphabet_translated_to_latin = [
  'a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','shch','','y','','e','yu','ya','A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','H','Ts','Ch','Sh','Shch','','Y','','e','Yu','Ya'
  ];

echo "Init successful.\n".PHP_EOL;

//----------------------------------------------------------------------------------------------------------------------------------//

if ($message == '/start' && $chat_id > 0) {
	sendMessage($chat_id, "Пришлите мне сломанное сообщение для перевода, добавьте меня в чат и вызовите там командой /fix, или в inline-режиме вставьте ваш сломанный текст.");
}

if ($query_id !== 'inline_query_id_empty') {
	sendInlineMessage($query_id, $query);

	$db = mysqli_connect($db_host, $db_username, $db_pass, $db_schema);
	if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
		else echo "MySQL connect successful.\n";

	mysqli_query($db, 'update main set translate_count=translate_count+1 where id=1');
	mysqli_close($db);
}

if ($chat_type == 'private') {
	if ($reply_message_text !== 'reply_message_empty') {
		sendReply($chat_id, strtr($reply_message_text, $translated_chars_array), $message_id);
	} else {
		sendReply($chat_id, strtr($message, $translated_chars_array), $message_id);
	}
	$db = mysqli_connect($db_host, $db_username, $db_pass, $db_schema);
	if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
		else echo "MySQL connect successful.\n";

	mysqli_query($db, 'update main set translate_count=translate_count+1 where id=1');
	mysqli_close($db);
}

if ($chat_type !== 'private') {
	$dictionary = file("rus_eng.txt");
	$words = explode(" ", $message);
	$word = $words[0];
	
	foreach ($dictionary as $line) {
		$line = trim($line);
		if ($word == $line) {
			sendReply($chat_id, strtr($message, $translated_chars_array), $message_id);
			
			$db = mysqli_connect($db_host, $db_username, $db_pass, $db_schema);
			if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
				else echo "MySQL connect successful.\n";
	
			mysqli_query($db, 'update main set translate_count=translate_count+1 where id=1');
			mysqli_close($db);
			break;
		}
	}
}

if ($message == '/fix' || $message == '/fix@fixmywordsbot') {
	if ($reply_message_text !== 'reply_message_empty') {
		$db = mysqli_connect($db_host, $db_username, $db_pass, $db_schema);
		if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
			else echo "MySQL connect successful.\n";

		mysqli_query($db, 'update main set translate_count=translate_count+1 where id=1');
		mysqli_close($db);
		sendReply($chat_id, strtr($reply_message_text, $translated_chars_array), $reply_message_id);
	} else {
		sendMessage($chat_id, 'Нечего переводить!');
	}
}
//----------------------------------------------------------------------------------------------------------------------------------//

//отправка форматированного сообщения
function sendMessage($chat_id, $message) {
	file_get_contents($GLOBALS['api'].'/sendMessage?chat_id='.$chat_id.'&text='.urlencode($message).'&parse_mode=Markdown');
}

function sendInlineMessage($query_id, $query) {
	$translated_text = strtr($query, $GLOBALS['translated_chars_array']);
	$transliterated_text = str_replace($GLOBALS['cyrillic_alphabet'], $GLOBALS['alphabet_translated_to_latin'], $query);
	$reverse_transliterated_text = str_replace($GLOBALS['alphabet_translated_to_latin'], $GLOBALS['cyrillic_alphabet'], $query);
	$result = [
	[
		'type' => 'article',
		'id' => '1',
		'title' => 'По-русски:',
		'input_message_content' => ['message_text' => $translated_text],
		'description' => $translated_text
	],
	[
		'type' => 'article',
		'id' => '2',
		'title' => 'Транслит:',
		'input_message_content' => ['message_text' => $transliterated_text],
		'description' => $transliterated_text
	],
	[
		'type' => 'article',
		'id' => '3',
		'title' => 'Обратный транслит:',
		'input_message_content' => ['message_text' => $reverse_transliterated_text],
		'description' => $reverse_transliterated_text
	]];
	file_get_contents($GLOBALS['api'].'/answerInlineQuery?inline_query_id='.$query_id.'&results='.json_encode($result));
}

function sendReply($chat_id, $message, $reply_id)
{
	file_get_contents($GLOBALS['api'].'/sendMessage?chat_id='.$chat_id.'&text='.urlencode($message).'&reply_to_message_id='.$reply_id);
}

echo "End script.".PHP_EOL;
?>
