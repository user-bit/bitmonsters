<?php class ImagesController extends BaseController
{
    /**
     * Загрузка фото (обычная)
     * ajax подгрузка
     */
    function updatePhotoAction()
    {
        if (!is_dir($_POST['path'])) mkdir($_POST['path'], 0755, true);

        $file = (isset($_FILES['image'])) ? $_FILES['image'] : $_POST['image'];
        if (isset($file, $_POST['path'])) {
            $copied_image = $this->createTmpImage($file, $_POST['path'], $_POST['type']);
            $result = ['image' => $copied_image . '?' . time(), 'status' => 'image successfully copied'];
            $this->delimageAction($_POST['table'], $this->db->cell("SELECT {$_POST['photo_field']} FROM {$_POST['table']} WHERE id=?", [$_POST['id']]));
            //перезапись картинки в папке
            $img = Images::loadOriginalImage($copied_image, $_POST['path'], $_POST['id'], $_POST['increment']).'?'.time();
            $result = ['image' => $img . '?' . time(), 'status' => 'Изображение записи обновлено'];
            $this->set_path_image($_POST['table'], $_POST['id'], $_POST['photo_field'], $img);

        } else $result = ['error' => 'Correct params was not given'];
        return json_encode($result);
    }

    function updateMultiPhotoAction()
    {
        if (!is_dir($_POST['path'])) mkdir($_POST['path'], 0755, true);
        $file = (isset($_FILES['image'])) ? $_FILES['image'] : $_POST['image'];
        $img = $this->createTmpImage($file, $_POST['path'], $_POST['type']);
        $new_id = $this->db->insert_id("INSERT INTO `{$_POST['table']}_photo` SET {$_POST['table']}_id=?,texture=?,active=?,sort=?", [$_POST['id'], 0, 1, 0]);
        $img = Images::loadOriginalImage($img, $_POST['path'], $new_id, '').'?'.time();

        $this->set_path_image($_POST['table'] . "_photo", $new_id, 'photo', $img);
        $res = $this->db->rows("SELECT * FROM `{$_POST['table']}_photo` tb WHERE {$_POST['table']}_id=? ORDER BY sort ASC,id DESC", [$_POST['id']]);
        $this->registry->set('administrator-cms', $_POST['table']);
        $content = $this->view->Render('photos/multi-photo/ajax_multi_img.phtml', array(
            'administrator-cms' => $_POST['table'],
            'photo' => $res,
            'action' => $_POST['table'],
            'path' => $_POST['path'],
            'sub_id' => $_POST['id']
        ));
        return json_encode(['content' => $content]);
    }

    function set_path_image($tb, $id, $field, $path)
    {
        $this->db->query("UPDATE " . $tb . " SET " . $field . "=? WHERE id=?",
            [$path, $id]);
    }

    /**
     * ==================================================constructor
     * Загрузка фото (мультиязычно)
     * ajax подгрузка
     */
    function updatePhotoLangAction()
    {
        if (!is_dir($_POST['path'])) mkdir($_POST['path'], 0755, true);
        $file = (isset($_FILES['image'])) ? $_FILES['image'] : $_POST['image'];
        if (isset($file, $_POST['path'])) {
            $copied_image = $this->createTmpImage($file, $_POST['path'], $_POST['type']);
            $result = ['image' => $copied_image . '?' . time(), 'status' => 'image successfully copied'];

            $this->delimageAction($_POST['table'],
                $this->db->cell("SELECT photo FROM " . $this->registry['key_lang_admin'] . "_{$_POST['table']} WHERE " . $_POST['table'] . "_id=?",
                    [$_POST['id']]));

            //перезапись картинки в папке
            $img = Images::loadOriginalImage($copied_image, $_POST['path'], $_POST['id'], $_POST['photo_link']).'?'.time();
            $result = ['image' => $img . '?' . time(), 'status' => 'Изображение записи обновлено'];
            $this->set_path_image_lang($_POST['table'], $_POST['id'], $img);

        } else $result = ['error' => 'Correct params was not given'];
        return json_encode($result);
    }
    function set_path_image_lang($tb, $id, $path)
    {
        $this->db->query("UPDATE " . $this->registry['key_lang_admin'] . "_" . $tb . " SET photo=? WHERE " . $tb . "_id=?",
            [$path, $id]);
    }

    /**
     * ==================================================constructor|info-block
     * Загрузка фото (конструктор)
     * ajax подгрузка
     */
    function updatePhotoConstructorAction()
    {
        if (!is_dir($_POST['path'])) mkdir($_POST['path'], 0755, true);
        $file = (isset($_FILES['image'])) ? $_FILES['image'] : $_POST['image'];
        if (isset($file, $_POST['path'])) {

            $copied_image = $this->createTmpImage($file, $_POST['path'], $_POST['type']);
            $result = ['image' => $copied_image . '?' . time(), 'status' => 'image successfully copied'];

            //перезапись картинки в папке
            $img = Images::loadOriginalImage($copied_image, $_POST['path'], $_POST['increment'], '').'?'.time();
            $result = ['image' => $img . '?' . time(), 'status' => 'Изображение записи обновлено'];


            $this->db->query("UPDATE " . $this->registry['key_lang_admin'] . "_" . $_POST['table'] . " SET value=?
                WHERE key_value=? AND " . $_POST['table'] . "_id=?",
                [$img, $_POST['photo_key'], $_POST['id']]);


        } else json_encode($result = ['error' => 'Correct params was not given']);
        return json_encode($result);
    }


    /**
     * ==================================================update
     * Обновление информации фото
     * ajax подгрузка
     */
    function updatePhotoInfoAction()
    {
        $result = '';
        if (isset($_POST['photo_id'])) {
            $new_url_img = $_POST['photo_path'] . $_POST['photo_link'];
            rename($_POST['photo_link_old'], $new_url_img);

            //изменения названия webp
            $old_name_webp = $_POST['photo_path'] . $_POST['photo_webp'];
            $new_url_img_webp = $_POST['photo_path'] . substr($_POST['photo_link'], 0,
                    strrpos($_POST['photo_link'], '.')) . '.webp';
            if (file_exists($old_name_webp))
                rename($old_name_webp, $new_url_img_webp);

            //изменения названия mobile
            $old_name_mobi = $_POST['photo_path'] . $_POST['photo_mobi'];
            $new_url_img_mobi = $_POST['photo_path'] . 'mobile-' . $_POST['photo_link'];
            if (file_exists($old_name_mobi))
                rename($old_name_mobi, $new_url_img_mobi);

            $this->db->query(
                "UPDATE " . $_POST['photo_table'] . " SET `" . $_POST['photo_field'] . "`=? WHERE id=?", [$new_url_img, $_POST['photo_id']]
            );

            if ($_POST['photo_table'] != 'product') {
                $this->db->query(
                    "UPDATE " . $this->registry['key_lang_admin'] . "_" . $_POST['photo_table'] . " SET 
                    `photo_alt`=?, `photo_title`=? 
                    WHERE " . $_POST['photo_table'] . "_id=?",
                    [$_POST['photo_alt'], $_POST['photo_title'], $_POST['photo_id']]
                );
            }

            $vars['edit'] = $this->db->row("SELECT * FROM " . $_POST['photo_table'] . " tb
                LEFT JOIN " . $this->registry['key_lang_admin'] . "_" . $_POST['photo_table'] . " lang_tb ON " . $_POST['photo_table'] . "_id = tb.id
                WHERE id =" . $_POST['photo_id']);
            $this->registry->set(PathToTemplateAdmin, '');

            if ($_POST['photo_table'] == 'product') {
                $tpl = 'photo/info_img.phtml';
            } else
                $tpl = 'info_img.phtml';

            $vars['photo_field'] = $_POST['photo_field'];
            $result = array(
                'content' => $this->view->Render('photos/' . $tpl, $vars),
                'info' => 'Данные успешно обновлены'
            );
        } else $result = ['error' => 'Correct params was not given'];
        return json_encode($result);
    }

    function updatePhotoInfoConstructorAction()
    {
        if (isset($_POST['photo_key']) || isset($_POST['photo_id'])) {
            $new_url_img = $_POST['photo_path'] . $_POST['photo_link'];
            rename($_POST['photo_link_old'], $new_url_img);

            //изменения названия webp
            $old_name_webp = $_POST['photo_path'] . $_POST['photo_webp'];
            $new_url_img_webp = $_POST['photo_path'] . substr($_POST['photo_link'], 0,
                    strrpos($_POST['photo_link'], '.')) . '.webp';
            if (file_exists($old_name_webp))
                rename($old_name_webp, $new_url_img_webp);

            //изменения названия mobile
            $old_name_mobi = $_POST['photo_path'] . $_POST['photo_mobi'];
            $new_url_img_mobi = $_POST['photo_path'] . 'mobile-' . $_POST['photo_link'];
            if (file_exists($old_name_mobi))
                rename($old_name_mobi, $new_url_img_mobi);

            if (isset($_POST['photo_id'])) {
                $this->db->query(
                    "UPDATE " . $this->registry['key_lang_admin'] . "_" . $_POST['photo_table'] . " SET
                    `photo_alt`=?, `photo_title`=?, `photo`=?
                    WHERE " . $_POST['photo_table'] . "_id=?",
                    [$_POST['photo_alt'], $_POST['photo_title'], $new_url_img, $_POST['photo_id']]
                );
                $vars['edit'] = $this->db->row("SELECT * FROM " . $this->registry['key_lang_admin'] . "_" . $_POST['photo_table'] . " WHERE " . $_POST['photo_table'] . "_id =" . $_POST['photo_id']);
                $path_file = 'photos/lang/info_img.phtml';
            }elseif (isset($_POST['photo_key'])) {
                $this->db->query(
                    "UPDATE " . $this->registry['key_lang_admin'] . "_" . $_POST['photo_table'] . " SET 
                    `photo_alt`=?, `photo_title`=?, `value`=?
                     WHERE `key_value`=? AND `" . $_POST['photo_table'] . "_id`=?",
                    [$_POST['photo_alt'], $_POST['photo_title'], $new_url_img, $_POST['photo_key'], $_POST['photo_constructor_id']]
                );
                $vars['edit'] = $this->db->row("SELECT * FROM " . $this->registry['key_lang_admin'] . "_" . $_POST['photo_table'] . " WHERE `" . $_POST['photo_table'] . "_id`=" . $_POST['photo_constructor_id'] . " AND `key_value`='" . $_POST['photo_key'] . "'");
                $path_file = 'photos/info_img.phtml';
            }

            $this->registry->set(PathToTemplateAdmin, '');
            $result = array(
                'content' => $this->view->Render($path_file, $vars),
                'info' => 'Данные успешно обновлены'
            );
        } else $result = ['error' => 'Correct params was not given'];

        return json_encode($result);
    }

    function updatePhotoInfoMobiAction()
    {
        if (!is_dir($_POST['file_path'])) mkdir($_POST['file_path'], 0755, true);
        $file = (isset($_FILES['file'])) ? $_FILES['file'] : $_POST['image'];
        if (isset($file, $_POST['file_path'])) {
            //загрузка картинки в папку
            $upload_dir = $_POST['file_path'] . $_POST['file_name'];
            if (rename($_FILES['file']['tmp_name'], $upload_dir)) {
                chmod($upload_dir, 0644);
            }

            $result = ['image' => $upload_dir . '?' . time(), 'status' => 'Изображение записи обновлено'];

        } else $result = ['error' => 'Correct params was not given'];
        return json_encode($result);
    }

    function updatePhotoInfoWebpAction()
    {
        if (!is_dir($_POST['file_path'])) mkdir($_POST['file_path'], 0755, true);
        $file = (isset($_FILES['file'])) ? $_FILES['file'] : $_POST['image'];
        if (isset($file, $_POST['file_path'])) {
            //загрузка картинки в папку
            $upload_dir = $_POST['file_path'] . $_POST['file_name'];
            if (rename($_FILES['file']['tmp_name'], $upload_dir)) {
                chmod($upload_dir, 0644);
            }

            $result = ['image' => $upload_dir . '?' . time(), 'status' => 'Изображение записи обновлено'];

        } else $result = ['error' => 'Correct params was not given'];
        return json_encode($result);
    }



    function createTmpImage($image, $path, $type)
    {
        $copied_image = Images::saveFromFile($image, $path);
        return $copied_image;
    }

    function delimageAction($tb = NULL, $path = NULL)
    {
        $data = [];
        $data['message'] = '';
        $tb = (isset($tb)) ? $tb : $_POST['action'];
        $path = (isset($path)) ? $path : $_POST['path'];
        if (!$this->model->checkAccess('edit', $tb)) {
            $data['access'] = messageAdmin('Отказано в доступе', 'error');
        } else {
            if (is_file($path)) {
                if (substr($path, 0, 1) == '/') $path = substr($path, 1, strlen($path));
                if (file_exists($path)) unlink($path);
            }
        }
        return json_encode($data);
    }
}