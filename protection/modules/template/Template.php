<?php class Template extends Model
{
    static $table = 'template';
    static $name = 'Шаблоны';

    public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    public static function getObject($registry)
    {
        return new self::$table($registry);
    }

    public function add()
    {
        if (isset($_POST['name'], $_POST['path']) && $_POST['name'] != "") {
            $this->insert(array(
                'name' => $_POST['name'],
                'path' => $_POST['path'],
            ));
            return messageAdmin('Данные успешно добавлены');
        }
        return messageAdmin('Заполнены не все обязательные поля', 'error');
    }

    public function save()
    {
        $message = messageAdmin('Заполнены не все обязательные поля', 'error');
        if (isset($_POST['name'], $_POST['path'])) {
            $this->update(array(
                'name' => $_POST['name'],
                'path' => $_POST['path'],
                'module_table' => $_POST['module_table'],
                'module_table_type' => $_POST['module_table_type'],
                'module_select' => $_POST['module_select'],
                'module_sort' => $_POST['module_sort'],
                'module_limit' => $_POST['module_limit']),
                [['id', '=', $_POST['id']]]);
            $message = messageAdmin('Данные успешно сохранены');
        }
        return $message;
    }

    public function getConstructorContentAdmin($id)
    {
        $vars['content_page'] = $this->db->rows("
                                SELECT 
                                tb.id, tb.sort,
                                template.path as template_path, template.name as template_name, template.module_table, template.module_table_type,
                                infoblocks.path as info_path, infoblocks.name as info_name
                                FROM constructor tb 
                                LEFT JOIN template ON template.id = tb.template_id 
                                LEFT JOIN infoblocks ON infoblocks.id = tb.infoblock_id 
                                WHERE tb.pages_id=" . $id . "
                                ORDER BY tb.sort ASC, tb.id ASC"
        );
        foreach ($vars['content_page'] as $content_page) {
            $vars['content_page_resource'] = $this->db->rows("
                                SELECT * FROM " . $this->registry['key_lang_admin'] . "_constructor_resource tb
                                WHERE tb.constructor_resource_id=" . $content_page['id']
            );
            foreach ($vars['content_page_resource'] as $info) {
                $vars['content_page_res'][$content_page['id']][$info['key_value']] = array(
                    'name_admin' => $info['name_admin'],
                    'value' => $info['value'],
                    'id' => $info['id'],
                    'photo_alt' => $info['photo_alt'],
                    'photo_title' => $info['photo_title']
                );
            }
            $vars['content_page_resource_text'] = $this->db->rows("
                                SELECT * FROM " . $this->registry['key_lang_admin'] . "_constructor_resource_text tb
                                WHERE tb.constructor_resource_id=" . $content_page['id']
            );
            foreach ($vars['content_page_resource_text'] as $info_text) {
                $vars['content_page_res_text'][$content_page['id']][$info_text['key_value']] = array(
                    'value' => $info_text['value']
                );
            }
        }
        return $vars;
    }

    public function getConstructorContentFront($id)
    {
        $vars['content_page'] = $this->db->rows("
                                SELECT 
                                tb.id, tb.sort, 
                                template.path as template_path, template.name as template_name, 
                                infoblocks.path as info_path, infoblocks.name as info_name, 
                                template.module_table, template.module_table_type, template.module_select, template.module_sort, template.module_limit 
                                FROM constructor tb 
                                LEFT JOIN template ON template.id = tb.template_id 
                                LEFT JOIN infoblocks ON infoblocks.id = tb.infoblock_id 
                                WHERE tb.pages_id=" . $id . "
                                ORDER BY tb.sort ASC, tb.id ASC"
        );
        foreach ($vars['content_page'] as $content_page) {
            //если инфобок $content_page['info_path'] - то ищем в модуле infoblock : если нет, то модуле template
            if (!empty($content_page['info_path'])) {
                $vars['content_info_id'] = $this->db->row("SELECT * FROM constructor tb 
                                                    LEFT JOIN infoblocks ON infoblocks.id = tb.infoblock_id
                                                    WHERE tb.id =".$content_page['id']);
                $content_info_resource = $this->db->rows("
                                SELECT * FROM " . $this->registry['key_lang'] . "_infoblocks tb 
                                WHERE tb.infoblocks_id=" . $vars['content_info_id']['id'] );

                    foreach ($content_info_resource as $key => $info_res) {
                        $vars['content_info_resource'][$content_page['id']][$info_res['key_value']] = array(
                            'value' => $info_res['value'],
                            'photo_alt' => $info_res['photo_alt'],
                            'photo_title' => $info_res['photo_title']
                        );
                    }
            }else {
                //ищем в модуле template
                //таблица с текстом (большой размер поля в базе)
                $vars['content_page_resource_text'] = $this->db->rows("
                                SELECT * FROM " . $this->registry['key_lang'] . "_constructor_resource_text tb
                                WHERE tb.constructor_resource_id =" . $content_page['id']
                );
                foreach ($vars['content_page_resource_text'] as $info_text) {
                    $vars['content_page_res_text'][$content_page['id']][$info_text['key_value']] = array(
                        'value' => $info_text['value']
                    );
                }
                $content_page_resource = $this->db->rows("
                                SELECT * FROM " . $this->registry['key_lang'] . "_constructor_resource tb
                                WHERE tb.constructor_resource_id=" . $content_page['id']
                );
                foreach ($content_page_resource as $info) {
                    $vars['content_page_res'][$content_page['id']][$info['key_value']] = array(
                        'value' => $info['value'],
                        'id' => $info['id'],
                        'photo_alt' => $info['photo_alt'],
                        'photo_title' => $info['photo_title']
                    );
                }
                //если надо выбрать с таблицы
                if (!is_null($content_page['module_table'])) {
                   if ($content_page['module_table'] == 'slider') {
                       $vars['content_video'] =
                           $this->db->rows("SELECT * FROM video tb
                                LEFT JOIN " . $this->registry['key_lang'] . "_video lang_tb ON lang_tb.video_id = tb.id 
                                WHERE tb.active='1' ORDER BY tb.sort ASC");
                       $vars['content_eco'] =
                           $this->db->rows("SELECT * FROM eco tb
                                LEFT JOIN " . $this->registry['key_lang'] . "_eco lang_tb ON lang_tb.eco_id = tb.id 
                                WHERE tb.active='1' ORDER BY tb.sort ASC");
                   }elseif ($content_page['module_table'] == 'roadmap' || $content_page['module_table'] == 'event' || $content_page['module_table'] == 'faq') {
                       $vars['modules'] = $this->db->row("SELECT id FROM modules WHERE modules.controller='" . $content_page['module_table'] . "'");
                       $vars['translate_' . $content_page['module_table']] = $this->get_lang("tb.modules_id='" . $vars['modules']['id'] . "'");
                       $vars['content_' . $content_page['module_table']] = $this->db->rows("SELECT " . $content_page['module_select'] . " FROM 
                                " . $content_page['module_table'] . " tb
                                LEFT JOIN " . $this->registry['key_lang'] . "_" . $content_page['module_table'] . " lang_tb ON lang_tb." . $content_page['module_table'] . "_id = tb.id 
                                WHERE tb.active='1' ORDER BY " . $content_page['module_sort']);
                   }
                }
            }
        }
        return $vars;
    }
}