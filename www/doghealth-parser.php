<?php

set_time_limit(0);
include 'simple_html_dom.php';

// �������, ��������� �� ������ �� ����� ������� � ����������� ��������
function delete44($str,$symbol='') 
{ 
    return($strpos=mb_strpos($str,$symbol))!==false?mb_substr($str,0,$strpos,'utf8'):$str;
}

// �������, ������ ���-������ ��������� ��������
function curliandia_get($url)
{
//������������� cURL � ������� ������
$ch = curl_init($url);
//��������� �����
usleep(rand (1000, 3000)) ;
//usleep(7000) ;
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'c:/WebServers/home/animalcare-parser.ru/www/cookies.txt'); //����������� ���� ��� 
curl_setopt($ch, CURLOPT_COOKIEJAR, 'c:/WebServers/home/animalcare-parser.ru/www/cookies.txt'); //����������� ���� ��� 
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_REFERER, 'http://doghealth.ru/');
//���������� (��������� ����������� �� �����)
$out = curl_exec($ch);
//�������� ����������
curl_close($ch);
return $out;
}

/* ����������� � ����� ������ */
$hostname = "localhost"; // rscx.ru - ��������/���� �������, � MySQL
$username = "animalcareparser"; // poly_test - ��� ������������
$password = "animalcareparser"; // y4mHO0Jf - ������ ������������
$dbName = "animalcareparser"; // poly_test - �������� ���� ������
 
/* ������� MySQL, � ������� ����� ��������� ������ */
$table = "zabolevania"; // prefix_topic_content
 
/* ������� ���������� */
mysql_connect($hostname, $username, $password) or die ("�� ���� ������� ����������");
 
/* �������� ���� ������. ���� ���������� ������ - ������� �� */
mysql_select_db($dbName) or die (mysql_error());
	  
// Create DOM from URL or file
$html = str_get_html(curliandia_get('http://doghealth.ru/'));

// ���� ��� ����� ������ �� ������� ���� ����������� �� ������ �������� �����������
$count = 0;
$count_vn = 71;
foreach($html->find("html/body/div[2]/div[1]/div[1]/div[3]/div[1]/div[1]/div[1]/div[2]/div[1]/ul/li/a") as $element_link_sfera_zabolevania)
{
    $count++;
	// �������������� ���������, ���������� � �������������
	if (($count>13) && ($count<20)){
		echo $count . '&nbsp;' ;
		$massiv_link_sfera_zabolevania[$count] = $element_link_sfera_zabolevania->href ;
		echo $massiv_link_sfera_zabolevania[$count] . '<br />';
		// ���� ��� ����� ������ �� ��������� �������� �����������
		$html_vn = str_get_html(curliandia_get($massiv_link_sfera_zabolevania[$count]));
		foreach($html_vn->find("html/body/div[2]/div[1]/div[1]/div[3]/div[2]/div/div[1]/div/div[1]/div/h3/a") as $element_link_zabol)
		{
			$count_vn++;
			echo '&nbsp;&nbsp;|&nbsp;' . $count_vn . '&nbsp;' ;
			$element_link_zabol2 = $element_link_zabol;
			$element_link_zabol = iconv('UTF-8', 'CP1251', $element_link_zabol);
			echo $element_link_zabol . '<br />';
			// ������ �� ��������� �������� �����������
			$massiv_link_zabol[$count_vn] = $element_link_zabol2->href ;
			//echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $massiv_link_zabol[$count_vn] . '<br />';
			
			// ������������ ����������� - ��������� ������
			$massiv_name_zabol[$count_vn] = $element_link_zabol2->innertext() ;
			$massiv_name_zabol[$count_vn] = iconv('UTF-8', 'CP1251', $massiv_name_zabol[$count_vn]);
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $massiv_name_zabol[$count_vn] . '<br />';
			// ���� ��� ����� ��������� ���������� � �����������
			$html_info_z = str_get_html(curliandia_get($massiv_link_zabol[$count_vn]));
			$count_str = 0;
			foreach($html_info_z->find("html/body/div[2]/div[1]/div[1]/div[3]/div[2]/div/div[1]/div/div[1]/div/div/p") as $text_zabol)
			{
				$count_str++;
				$text_zabol = iconv('UTF-8', 'CP1251', $text_zabol);
				// �����������, ���� ����� ��������� ����� �������� ������
				if (substr_count($text_zabol , '<p class="postmetadata alt">')>0) {
					break;
				}
				// ������ ������ ��������� �������� �������� ����������� - ������ ������
				if ($count_str < 2) {
					$massiv_anons_zabol[$count_vn] = $text_zabol;
				}
				// ��������� �������� ����������� - ����� ������
				$massiv_kontent_zabol[$count_vn] = $massiv_kontent_zabol[$count_vn] . $text_zabol;
				//echo $text_zabol;
			}
			// ���������� ������ � ��� <noindex>...</noindex>
			$massiv_anons_zabol[$count_vn] = '<noindex>' . $massiv_anons_zabol[$count_vn] . '</noindex>';
			// ���������� �������� � ��� <noindex>...</noindex>
			$massiv_kontent_zabol[$count_vn] = '<noindex>' . $massiv_kontent_zabol[$count_vn] . '</noindex>';
			// ������ ������ ��������� �������� �������� ����������� - ������ ������
			// $massiv_anons_zabol[$count_vn] = delete44($massiv_kontent_zabol[$count_vn] ,'</p>');
			echo '<br />����� ������:&nbsp;&nbsp;' . $massiv_anons_zabol[$count_vn];
			echo '<br />����� ������:&nbsp;&nbsp;' . $massiv_kontent_zabol[$count_vn] . '<br />';

		}

	}

// break;
}

echo '<br /><p>' . $count_vn . ' - ������� ����� ������������ ����������� ��������</p>';

// ��������� � �� ���������� � ����������� - ������ - ����� ������ � ������� "zabolevania"
for ($counter = 72; $counter < $count_vn + 1; $counter++)
{
	$query = "INSERT INTO $table (id, title, body, meta_title, anons) VALUES ('".$counter."', '".$massiv_name_zabol[$counter]."', '".$massiv_kontent_zabol[$counter]."', '".$massiv_name_zabol[$counter]."', '".$massiv_anons_zabol[$counter]."')";
	mysql_query($query) or die(mysql_error());
}

/* ��������� ���������� */
mysql_close();