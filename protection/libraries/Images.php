<?php class Images extends Model
{
    public function __construct($registry)
    {
        parent::getInstance($registry);
    }
    /**
     * Загрузка фото (при создании раздела) - обычная
     */
    function savePhoto($id, $photo_name, $tmp_image, $tb, $no_info='')
    {
        $ext = 'jpg'; // default
        $ext_arr = explode(',', ext_image);
        foreach ($ext_arr as $e) {
            if (file_exists('storage/tmp/' . $tmp_image . '.' . $e)) {
                $ext = pathinfo('storage/tmp/' . $tmp_image . '.' . $e, PATHINFO_EXTENSION);
            }
        }
        $ext = strtolower($ext);
        if (isset($tmp_image) && file_exists('storage/tmp/' . $tmp_image . '.' . $ext)) {
            if (!empty($_POST['link'])) $link_photo = $photo_name;
            else $link_photo = $id;
            //Загрузка фото в папку при создании (add)
            $dir = Dir::createDir($id, '', $tb);
            $tmp_photo = 'storage/tmp/' . $tmp_image . '.' . $ext;
            copy($tmp_photo, $dir['0'] . $link_photo . "." . $ext);
            unlink($tmp_photo);

            $this->photo_del("storage/tmp/", $tmp_image . "." . $ext);

            $this->db->query("UPDATE ".$tb." SET `photo`=?
            WHERE id=?", [$dir['0'] . $link_photo . "." . $ext, $id]);
        }
    }

    /**
     * Загрузка фото (при создании раздела) - мультиязычная
     */
    function savePhotoLang($id, $photo_name, $tmp_image, $tb)
    {
        $ext = 'jpg'; // default
        $ext_arr = explode(',', ext_image);
        foreach ($ext_arr as $e) {
            if (file_exists('storage/tmp/' . $tmp_image . '.' . $e)) {
                $ext = pathinfo('storage/tmp/' . $tmp_image . '.' . $e, PATHINFO_EXTENSION);
            }
        }
        $ext = strtolower($ext);
        if (isset($tmp_image) && file_exists('storage/tmp/' . $tmp_image . '.' . $ext)) {
            if (!empty($_POST['link'])) $link_photo = $photo_name;
            else $link_photo = $id;
            //Загрузка фото в папку при создании (add)
            $dir = Dir::createDirLang($id,  $tb,  $this->registry['key_lang_admin']);
            $tmp_photo = 'storage/tmp/' . $tmp_image . '.' . $ext;
            copy($tmp_photo, $dir['0'] . $link_photo . "." . $ext);
            unlink($tmp_photo);

            $this->photo_del("storage/tmp/", $tmp_image . "." . $ext);

            $this->db->query("UPDATE ".$this->registry['key_lang_admin']."_".$tb." SET 
            `photo`=?, `photo_link`=?, `photo_title`=?, `photo_alt`=? 
            WHERE ".$tb."_id=?", [$dir['0'] . $link_photo . "." . $ext, $link_photo . '.' . $ext, $_POST['photo_title'], $_POST['photo_alt'], $id]);
        }
    }

    public function photo_del($dir, $id)
    {
        if (file_exists("{$dir}{$id}.jpg"))
            unlink("{$dir}{$id}.jpg");
    }

    static function saveFromFile($file, $path)
    {
        if (self::validateFileByExt($file['name'])) {
            $extensions = explode('.', $file['name']);
            $ext = end($extensions);
            $destination_file = $path . 'tmp.' . strtolower($ext);
            copy($file['tmp_name'], $destination_file);
            return $destination_file;
        } else new Exception('incorrect file type');
    }

    static function validateFileByExt($file)
    {
        $ext = explode('.', $file);
        $ext = end($ext);
        $valid_ext = explode(',', ext_image.','.ext_vide);
        return (in_array(strtolower($ext), $valid_ext)) ? true : false;
    }

    static function loadOriginalImage($image, $path_to_save, $id, $increment)
    {
        $ext = self::getFileExtension($image);
        
        $link_photo = $id . $increment .'.' . $ext;

        $destination_image = $path_to_save . $link_photo;

        if (copy($image, $destination_image)) {
            $destination_imag = (str_replace('.' . $ext, '.' . $ext, $destination_image));
            unlink($image);
            return $destination_imag;
        } else return false;
    }

    static function getFileExtension($file)
    {
        $parsed = explode('.', $file);
        return end($parsed);
    }
}
