<?php

set_time_limit(0);
include 'simple_html_dom.php';

// Функция, удаляющая из строки всё после встречи с определённым символом
function delete44($str,$symbol='') 
{ 
    return($strpos=mb_strpos($str,$symbol))!==false?mb_substr($str,0,$strpos,'utf8'):$str;
}

// Функция, забора дом-дерева указанной страницы
function curliandia_get($url)
{
//Инициализация cURL и задание адреса
$ch = curl_init($url);
//Установка опций
usleep(rand (1000, 3000)) ;
//usleep(7000) ;
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'c:/WebServers/home/animalcare-parser.ru/www/cookies.txt'); //Подставляем куки раз 
curl_setopt($ch, CURLOPT_COOKIEJAR, 'c:/WebServers/home/animalcare-parser.ru/www/cookies.txt'); //Подставляем куки два 
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_REFERER, 'http://cathealth.ru/');
//выполнение (результат отобразится на экран)
$out = curl_exec($ch);
//Закрытие соединения
curl_close($ch);
return $out;
}

/* Соединяемся с базой данных */
$hostname = "localhost"; // rscx.ru - название/путь сервера, с MySQL
$username = "animalcareparser"; // poly_test - имя пользователя
$password = "animalcareparser"; // y4mHO0Jf - пароль пользователя
$dbName = "animalcareparser"; // poly_test - название базы данных
 
/* Таблица MySQL, в которой будут храниться данные */
$table = "zabolevania"; // prefix_topic_content
 
/* Создаем соединение */
mysql_connect($hostname, $username, $password) or die ("Не могу создать соединение");
 
/* Выбираем базу данных. Если произойдет ошибка - вывести ее */
mysql_select_db($dbName) or die (mysql_error());
	  
// Create DOM from URL or file
$html = str_get_html(curliandia_get('http://cathealth.ru/'));

// Цикл для сбора ссылок на разделы сфер заболеваний со своими списками заболеваний
$count = 0;
$count_vn = 374;
foreach($html->find("//ul.level1/li/a") as $element_link_sfera_zabolevania)
{
    $count++;
	// Ограничиваемся разделами, связанными с заболеваниями
	if (($count>15) && ($count<19)){
		echo $count . '&nbsp;' ;
		$massiv_link_sfera_zabolevania[$count] = 'http://cathealth.ru' . $element_link_sfera_zabolevania->href ;
		echo $massiv_link_sfera_zabolevania[$count] . '<br />';
		// Цикл для сбора ссылок на подробные описания заболеваний
		$html_vn = str_get_html(curliandia_get($massiv_link_sfera_zabolevania[$count]));
		foreach($html_vn->find("//div[@id='content-left']/div/div.article-right/h5/a") as $element_link_zabol)
		{
			$count_vn++;
			echo '&nbsp;&nbsp;|&nbsp;' . $count_vn . '&nbsp;' ;
			$element_link_zabol2 = $element_link_zabol;
			$element_link_zabol = iconv('UTF-8', 'CP1251', $element_link_zabol);
			echo $element_link_zabol . '<br />';
			// Ссылка на подробное описание заболевания
			$massiv_link_zabol[$count_vn] = $element_link_zabol2->href ;
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $massiv_link_zabol[$count_vn] . '<br />';
			
			// Наименование заболевания - заголовок статьи
			$massiv_name_zabol[$count_vn] = $element_link_zabol2->innertext() ;
			$massiv_name_zabol[$count_vn] = iconv('UTF-8', 'CP1251', $massiv_name_zabol[$count_vn]);
			// Удаляем лишние пробелы в заголовке статьи
			$massiv_name_zabol[$count_vn] = preg_replace('/\s+/i', ' ', $massiv_name_zabol[$count_vn]);
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $massiv_name_zabol[$count_vn] . '<br />';
			
			// Сбор подробной информации о заболевании
			$html_info_z = str_get_html(curliandia_get($massiv_link_zabol[$count_vn]));
			// Формируем описание заболивания - текст статьи
			$massiv_kontent_zabol[$count_vn] = implode("", $html_info_z->find("//div[@id='content-left']/div[2]/div.article-right "));
			$massiv_kontent_zabol[$count_vn] = iconv('UTF-8', 'CP1251', $massiv_kontent_zabol[$count_vn]);
			// Удаляем лишние пробелы из контента статьи
			$massiv_kontent_zabol[$count_vn] = preg_replace('/\s+/i', ' ', $massiv_kontent_zabol[$count_vn]);
			// Удаляем рубрикаторную часть из контента статьи
			$massiv_kontent_zabol[$count_vn] = preg_replace('/<p><span(.+?)class="article-meta"(.+?)>(.+?)<\/span><\/p>/i', '', $massiv_kontent_zabol[$count_vn]);
			
			// Получаем краткое описание заболевания - анонса статьи
			$massiv_anons_zabol[$count_vn] = implode("", $html_info_z->find("//div[@id='content-left']/div[2]/div.article-right/p[2] "));
			$massiv_anons_zabol[$count_vn] = iconv('UTF-8', 'CP1251', $massiv_anons_zabol[$count_vn]);
			// Удаляем лишние пробелы из анонса статьи
			$massiv_anons_zabol[$count_vn] = preg_replace('/\s+/i', ' ', $massiv_anons_zabol[$count_vn]);

			// Заключение анонса в тег <noindex>...</noindex>
			$massiv_anons_zabol[$count_vn] = '<noindex>' . $massiv_anons_zabol[$count_vn] . '</noindex>';
			// Заключение контента в тег <noindex>...</noindex>
			$massiv_kontent_zabol[$count_vn] = '<noindex>' . $massiv_kontent_zabol[$count_vn] . '</noindex>';

			echo '<br />Анонс статьи:&nbsp;&nbsp;' . $massiv_anons_zabol[$count_vn];
			echo '<br />Текст статьи:&nbsp;&nbsp;' . $massiv_kontent_zabol[$count_vn] . '<br />';

		}

	}

// break;
}

echo '<br /><p>' . $count_vn . ' - Сколько всего наименований заболеваний спаршено</p>';

// Добавляем в БД информацию о заболевании - статью - новую строку в таблицу "zabolevania"
for ($counter = 375; $counter < $count_vn + 1; $counter++)
{
	$query = "INSERT INTO $table (id, title, body, meta_title, anons) VALUES ('".$counter."', '".$massiv_name_zabol[$counter]."', '".$massiv_kontent_zabol[$counter]."', '".$massiv_name_zabol[$counter]."', '".$massiv_anons_zabol[$counter]."')";
	mysql_query($query) or die(mysql_error());
}


/* Закрываем соединение */
mysql_close();