<?php class Dir
{
    static function removeDir($dir)
    {
        if ($objs = glob($dir . "/*")) foreach ($objs as $obj) is_dir($obj) ? Dir::removeDir($obj) : unlink($obj);
        if (is_dir($dir)) rmdir($dir);
    }

    // Remove all dir with name $name_dir
    static function bfglob($path, $name_dir, $pattern = '*', $flags = GLOB_NOSORT, $depth = 0)
    {
        $matches = [];
        $folders = [rtrim($path, DIRECTORY_SEPARATOR)];
        $i = 0;
        while ($folder = array_shift($folders)) {
            $matches = array_merge($matches, glob($folder . DIRECTORY_SEPARATOR . $pattern, $flags));
            if ($depth != 0) {
                $moreFolders = glob($folder . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
                $depth = ($depth < -1) ? -1 : $depth + count($moreFolders) - 2;
                $folders = array_merge($folders, $moreFolders);
                $i++;
            }
        }
        for ($i = 0; $i <= count($matches) - 1; $i++) {
            if (strpos($matches[$i], $name_dir) !== false) {
                removeDir($matches[$i]);
                echo $matches[$i] . '<br>';
            }
        }
        return $matches;
    }

    static function get_directory_list($path, $except = [])
    {
        $list_dir = [];
        $dir = opendir($path);
        while ($file_name = readdir($dir)) {
            clearstatcache();
            if (is_dir($path . $file_name) && $file_name != '..') {
                if (is_array($except) && !in_array($file_name, $except))
                    array_push($list_dir, $file_name);
            }
        }
        return $list_dir;
    }

    //удалить все файлы в указаной папке
    static function remove_files_in_dir($path)
    {
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) if ($file != "." && $file != "..") unlink($path . $file);
            closedir($handle);
        }
    }

    static function get_file_list($dir)
    {
        $list_file = array();
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) if ($file != '.' && $file != '..' && !is_dir($dir . $file)) {
                    $time = date("Y-m-d H:i:s", filemtime($dir . $file));
                    array_push($list_file, ['name' => $file, 'time' => $time, 'path' => $dir . $file]);
                }
                closedir($dh);
            }
        }
        return $list_file;
    }

    static function createDirLang($id, $table, $admin_lang)
    {
        if ($id != '') {
            $dir = [];
            $dir['0'] = "storage/" . $table . "/" . substr($id, -1) . '/' . $id . '/' . $admin_lang . '/';
            if (!is_dir( $dir['0'])) mkdir( $dir['0'], 0755, true);
            return $dir;
        }
    }

    static function createDir($id, $path = '', $table = 'product')
    {
        if ($id != '') {
            $dir = [];
            $dir['0'] = "storage/" . $table . "/" . substr($id, -1) . '/' . $id . '/';
            $dir['1'] = "storage/" . $table . "/" . substr($id, -1) . '/' . $id . '/more/';
            if (!is_dir($path . $dir['1'])) mkdir($path . $dir['1'], 0755, true);
            return $dir;
        }
    }
}