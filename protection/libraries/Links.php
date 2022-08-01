<?php class Links
{
    public static function getAdminURl($url, $no_link = '')
    {
        if (empty($url)) {
            if (!empty(LINK_ADMIN)) {
                $url = LINK_ADMIN;
            } else {
                $url = '/';
            }
        } else {
            $url = LINK_ADMIN . '/' . $url;
        }
        $url = str_replace('//', '/', $url);
        return $url;
    }

    public static function getURl($link, $get = "")
    {
        if (empty($link)) {
            if (!empty(LINK)) {
                $link = LINK . '/';
            } else {
                $link = '/';
            }
        } else {
            if (!empty($get))
                $link = LINK . '/' . $link;
            else
                $link = LINK . '/' . $link . '/';
        }
        $link = str_replace('//', '/', $link);
        return $link;
    }

    public static function getPhoto($photo, $fancybox = '', $no_lazy = '', $alt = '', $schema='')
    {
        if (isset($photo['value']))
            $link_photo = $photo['value'];
        else
            $link_photo = $photo['photo'];

        if (empty($link_photo))
            $link_photo = 'resource/front/img/default-product.jpg';

        $path = substr($link_photo, 0, strrpos($link_photo, '/'));
        $path_webp = substr($link_photo, 0, strrpos($link_photo, '.')) . '.webp';
        $path_mobi = $path . '/mobile-' . substr($link_photo, ($a = strrpos($link_photo, '/') + 1));

        if (file_exists($path_webp))
            $photo_webp = $path_webp;
        if (file_exists(strtok($path_mobi, '?')))
            $photo_mobile = $path_mobi;


        if (!empty($photo['photo_alt']))
            $photo_alt = $photo['photo_alt'];
        else
            $photo_alt = $alt;

        $photo_title = $photo['photo_title'];

        $photo = "<picture>";
        if (!empty($fancybox))
            $photo .= "<a href='/" . $link_photo . "'>";
        if (isset($photo_webp))
            $photo .= "<source class='lazy' data-srcset='/" . $photo_webp . "' type='image/webp'>";
        if (isset($photo_mobile))
            $photo .= "<source srcset   ='/" . $photo_mobile . "' media='(max-width: 575px)'>";


        if (!empty($no_lazy))
            $photo .= "<img src='/" . $link_photo . "' alt='" . $photo_alt . "' title='" . $photo_title . "'>";
        else
            $photo .= "<img ".$schema." class='lazy' data-src='/" . $link_photo . "' alt='" . $photo_alt . "' title='" . $photo_title . "'>";


        if (!empty($fancybox))
            $photo .= "</a>";
        $photo .= "</picture>";

        return $photo;
    }
}
