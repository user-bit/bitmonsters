<?php class Arr
{
    static function treeview($settings, $sub_id = 0)
    {
        $return = '';
        foreach ($settings['arr'] as $row) {
            if ($row['sub'] == $sub_id) {
                $return .= '<li><a href="' . Links::getAdminURl($settings['link'] . $row['link']) . '">' . $row['name'] . '</a>' . Arr::treeview($settings, $row['id']) . '</li>';
                if (isset($settings['separator'])) {
                    $return .= '<li class="' . $settings['separator'] . '"></li>';
                }
            }
        }
        if ($return != '') {
            if ($sub_id == 0) $id = 'id="' . $settings['id'] . '"';
            else $id = '';
            $return = '<ul ' . $id . '>' . $return . '</ul>';
        }
        return $return;
    }
}