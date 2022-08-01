<?php class MetaController extends BaseController
{
    protected $params;
    protected $db;

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = "meta";
        $this->registry = $registry;
        $this->meta = new Meta($this->sets);
    }

    function sitemapAction()
    {
        $filename = 'sitemap.xml';
        $filePath = './' . $filename;
        header("Content-type: text/xml; charset=utf-8");
        //Если включен динамический сайтмап или сайтмапа пока нету
        if ((int)$this->settings['sitemap_generation'] === 1 or !file_exists($filePath)) {
            $sitemap = $this->meta->sitemap_generate();
        } else {
            $sitemap = file_get_contents($filePath);
        }
        echo $sitemap;
    }
}