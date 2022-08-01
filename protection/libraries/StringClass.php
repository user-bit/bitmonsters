<?php class StringClass
{
    static function translit($string, $flag = false)
    {
        $replace = [' ' => '', "'" => "", "ϟ" => "", "·" => "", "©" => "", "´" => "", "°" => "", "’" => "", "`" => "", "«" => "", "»" => "", ":" => "", "“" => "", "•" => "", "●" => "", "́" => "", "|" => "", "″" => "", "…" => "", "‘" => "", "™" => "", "♔" => "", "↔" => "", "▲" => "", "ჳ" => "", "ჴ" => "", "а" => "a", "А" => "a", "Ä" => "a", "ä" => "a", "Â" => "a", "â" => "a", "Ă" => "a", "ă" => "a", "Ą" => "a", "ą" => "a", "ა" => "a", "ã" => "a", "ბ" => "b", "б" => "b", "Б" => "b", "в" => "v", "В" => "v", "ვ" => "v", "г" => "g", "Г" => "g", "ґ" => "g", "Ґ" => "g", "გ" => "g", "ღ" => "g", "ģ" => "g", "д" => "d", "Д" => "d", "დ" => "d", "е" => "e", "Е" => "e", "Ę" => "e", "ę" => "e", "ე" => "e", "é" => "e", "ê" => "e", "Ӂ" => "zh", "ӂ" => "zh", "Ź" => "zh", "ź" => "zh", "Ż" => "zh", "ż" => "zh", "ж" => "zh", "Ж" => "zh", "ჟ" => "zh", "з" => "z", "З" => "z", "ზ" => "z", "и" => "i", "И" => "i", "ი" => "i", "Ι" => "i", "й" => "y", "Й" => "y", "й" => "y", "к" => "k", "К" => "k", "კ" => "k", "ქ" => "k", "Ł" => "l", "ł" => "l", "л" => "l", "Л" => "l", "ლ" => "l", "м" => "m", "М" => "m", "მ" => "m", "н" => "n", "Н" => "n", "ნ" => "n", "ń" => "n", "о" => "o", "Ö" => "o", "ö" => "o", "Ó" => "o", "ó" => "o", "О" => "o", "ო" => "o", "Ø" => "o", "п" => "p", "П" => "p", "პ" => "p", "ფ" => "p", "р" => "r", "Р" => "r", "რ" => "r", "Ś" => "s", "ś" => "s", "ẞ" => "s", "ß" => "s", "с" => "s", "С" => "s", "ს" => "s", "т" => "t", "Т" => "t", "თ" => "t", "ტ" => "t", "Τ" => "t", "у" => "u", "У" => "u", "უ" => "u", "ф" => "f", "Ф" => "f", "х" => "h", "Х" => "h", "ჰ" => "h", "Ț" => "c", "ț" => "c", "ц" => "c", "Ц" => "c", "ც" => "c", "č" => "c", "Ć" => "ch", "ć" => "ch", "ч" => "ch", "Ч" => "ch", "ჭ" => "ch", "Ș" => "sh", "ș" => "sh", "ჩ" => "ch", "ш" => "sh", "Ш" => "sh", "შ" => "sh", "щ" => "sch", "Щ" => "sch", "ъ" => "", "Ъ" => "", "Î" => "y", "î" => "y", "Ü" => "y", "ü" => "y", "ы" => "y", "Ы" => "y", "ь" => "", "Ь" => "", "э" => "e", "Э" => "e", "ю" => "yu", "Ю" => "yu", "я" => "ya", "Я" => "ya", "і" => "i", "І" => "i", "ї" => "yi", "Ї" => "yi", "є" => "e", "Є" => "e", "ё" => "e", "Ё" => "e", "ყ" => "q", "ძ" => "dz", "ხ" => "kh", "ჯ" => "j", "წ" => "ts", "	" => "-", " " => "-", "„" => "-", "”" => "-", "_" => "-", "?" => "-", "–" => "-", "—" => "-", "−" => "-", "№" => "no", "®" => "", '⧺' => '', '∫' => '', '大' => '', '☆' => ''];
        $string = iconv("UTF-8", "UTF-8//IGNORE", strtr($string, $replace));
        if (!$flag) $string = preg_replace("/[^a-zA-ZА-Яа-я0-9\s\_]/", "-", $string);
        else $string = preg_replace("/[^a-zA-ZА-Яа-я0-9\s\/\:]/", "-", $string);
        $string = self::delete_duplicated_chars($string, '-');
        return $string;
    }

    //Deleting dublicating strings
    static function delete_duplicated_chars($string, $char_to_delete)
    {
        $word = '';
        for ($i = 0; $i < strlen($string); $i++) $string[$i] == $char_to_delete && $string[$i + 1] == $char_to_delete ? '' : $word .= $string[$i];
        $word[strlen($word) - 1] == $char_to_delete ? $word = substr($word, 0, -1) : '';
        return $word;
    }

    static function sanitize($var, $reverse = false)
    {
        $sanMethod = [['&amp;', '&'], ['&', '&#038;'], ['"', '&#034;'], ['"', '&quot;'], ["'", '&#039;'], ['%', '&#037;'], ['(', '&#040;'], [')', '&#041;'], ['+', '&#043;'], ['<', '&lt;'], ['>', '&gt;'], ['\'', '&apos;'], ['&', '&amp;'], ['«', '&laquo;'], ['»', '&raquo;'], ['-', '&ndash;'], ['“', '&ldquo;'], ['”', '&rdquo;'], ['—', '&mdash;'], ['±', '&plusmn;'], ['°', '&deg;'], [' ', '&nbsp;']];
        if (!is_array($var)) {
            $charsCount = count($sanMethod);
            if ($reverse) for ($j = $charsCount; $j > 0; $j--) if (isset($sanMethod[$j][1])) $var = str_replace($sanMethod[$j][1], $sanMethod[$j][0], $var);
            else for ($j = 0; $j < $charsCount; $j++) if (isset($sanMethod[$j][0])) $var = str_replace($sanMethod[$j][0], $sanMethod[$j][1], $var);
            return $var;
        }
        $varCount = count($var);
        $keys = array_keys($var);
        $i = 0;
        while ($i < $varCount) {
            if (is_array($var[$keys[$i]])) return StringClass::sanitize($var[$keys[$i]]);
            else {
                $charsCount = count($sanMethod);
                if ($reverse) for ($j = $charsCount; $j > 0; $j--) $var = str_replace($sanMethod[$j][1], $sanMethod[$j][0], $var);
                else for ($j = 0; $j < $charsCount; $j++) $var[$keys[$i]] = str_replace($sanMethod[$j][0], $sanMethod[$j][1], $var[$keys[$i]]);
            }
            $i++;
        }
        return $var;
    }

    static function replace_tag($string)
    {
        $string = preg_replace('/[^\p{L}\p{N}\s]/u', '', $string);
        $replace = array('ltpgt'=>'','nbspltpgt'=>'','ltstronggt'=>'','quotltstronggtnbsp'=>'','quot'=>'','ndash'=>'-', 'mdash'=>'-',
            'ltp'=>'','justify'=>'', 'gt'=>'','style'=>'','textalign'=>'','ltem'=>'','right'=>'');
        $string = iconv("UTF-8", "UTF-8//IGNORE", strtr($string, $replace));
        return $string;
    }

    static function post_write($err, $header = false)
    {
        $_POST['err'] = $err;
        $_SESSION['_POST'] = $_POST;
        $_POST = [];
        if ($header) {
            header("location:$header");
            exit();
        }
    }

    static function post_read()
    {
        if (empty($_SESSION['_POST'])) return [];
        $post = $_SESSION['_POST'];
        unset($_SESSION['_POST']);
        return $post;
    }

    static function get_page_link($page, $cur_page, $var, $text = '')
    {

        if (!$text) $text = $page;
        if ($page != $cur_page) {
            if (!empty($_GET['word']))
                $page_get = '?word=' . str_replace(' ', '+', $_SESSION['search_word']);

            $path = $_SERVER['REQUEST_URI'];
            $reg = '/((\/|^)' . $var . '\/)[^\/#]*/';
            if ($page != 1) $url = (preg_match($reg, $path) ? preg_replace($reg, '${1}' . $page, $path) : ($path ? $path . '/' : '') . $var . '/' . $page) . '/';
            else $url = (preg_match($reg, $path) ? preg_replace($reg, '', $path) : ($path ? $path . '/' : '') . $var . '/' . $page) . '/';
            if (isset($_SESSION['key_lang']) && $_SESSION['key_lang'] != LANG) {
                $url = '/' . $_SESSION['key_lang'] . $url . '/';
            }
            $url = str_replace("//", "/", $url) . $page_get;
            return '<li class="paging__item" rel="canonical"><a href="' . $url . '" class="paging__link">' . $text . '</a></li>';
        }

        return '<li class="paging__item active" rel="canonical" ><span class="paging__link">' . $text . '</span></li>';
    }

    /**
     * @param $page
     * @param $var
     * @return StringClass $url
     * Метод был создан для разбиения получения чистого url из метода get_page_link и html кода
     * Данный метод возвращает только url
     */
    static function getPageUrl($page, $var)
    {
        $path = $_SERVER['REQUEST_URI'];
        $reg = '/((\/|^)' . $var . '\/)[^\/#]*/';
        if ($page != 1) $url = (preg_match($reg, $path) ? preg_replace($reg, '${1}' . $page, $path) : ($path ? $path . '/' : '') . $var . '/' . $page);
        else $url = (preg_match($reg, $path) ? preg_replace($reg, '', $path) : ($path ? $path . '/' : '') . $var . '/' . $page);
        $url = LINK . $url;
        return str_replace("//", "/", $url);
    }

    static function getUrl3($var)
    {
        $url = str_replace($var, '', $_SERVER['REQUEST_URI']) . '/';
        $url = str_replace("//", "/", $url);
        if ($url == '') $url = '/';
        return $url;
    }

    static function getUrl2($var, $str = '', $clear = false)
    {
        if ($str == '') $str = $_SERVER['REQUEST_URI'];
        $reg = '/' . $var . '\/[a-z0-9-]+/';
        $url = preg_replace($reg, '', $str);
        if ($clear == true) {
            if (isset($_SESSION['key_lang']) && $_SESSION['key_lang'] != LANG) $url = '/' . $_SESSION['key_lang'] . $url;
        }
        $url = str_replace("//", "/", $url);
        return $url;
    }

    static function getUrl($var, $page = '')
    {
        $path = $_SERVER['REQUEST_URI'];
        $reg = '/((\/|^)' . $var . '\/)[^\/#]*/';
        $url = (preg_match($reg, $path) ? preg_replace($reg, '${1}' . $page, $path) : ($path ? $path . '/' : '') . $var . '/' . $page);
        if (isset($_SESSION['key_lang']) && $_SESSION['key_lang'] != LANG) $url = '/' . $_SESSION['key_lang'] . $url;
        $url = str_replace("//", "/", $url);
        return $url;
    }

    static function search_links($text)
    {
        $str = '';
        preg_match_all('/(?:href|src|url)=(\"?)((http\:\/\/)[^\s\">]+?)(\"?)([^>]*>)/ismU', $text, $links);
        foreach ($links[2] as $link) {
            if ($str != '') $str .= ',';
            $str .= $link;
        }
        return $str;
    }

    static function clear_links($text)
    {
        $text = preg_replace('~<a\b[^>]*+>|</a\b[^>]*+>~', '', $text);
        return $text;
    }
    function ru_plural ($number, $titles = array())
    {
//      в каком порядке нужно передавать массив
//      1 -- комментарий
//      2 -- комментария
//      5 -- комментариев
        $cases = array (2, 0, 1, 1, 1, 2);
        return $number.' '.$titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
}