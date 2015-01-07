<?php
/*
Plugin Name:    Quickgallery
Plugin URI:     https://github.com/jmversteeg/quickgallery
Description:    The WordPress gallery that just works
Version:        0.0.2
Author:         jmversteeg
Author URI:     https://github.com/jmversteeg
*/

class QG
{

    public static $templatedir = array("/templates/");

    const TEMPLATE_PIC    = "pic.php";
    const TEMPLATE_VIEW   = "view.php";
    const TEMPLATE_FOOTER = "footer.php";

    /**
     * @var QGView
     */
    private static $currentView;
    /**
     * @var QGPic
     */
    private static $currentPic;

    /**
     * Searches for the given $filename in relative $templatedir and includes it if it exists.
     *
     * @param $filename
     *
     * @return bool Whether the file was included succesfully.
     */
    public static function getTemplate ($filename)
    {
        foreach (self::$templatedir as $templatedir) {
            $file = __DIR__ . $templatedir . $filename;
            if (file_exists($file)) {
                include $file;
                return true;
            }
        }
        return false;
    }

    /**
     * @return QGView
     */
    public static function getCurrentView ()
    {
        return self::$currentView;
    }

    /**
     * @param QGView $currentView
     */
    public static function setCurrentView ($currentView)
    {
        self::$currentView = $currentView;
    }

    /**
     * @return QGPic
     */
    public static function getCurrentPic ()
    {
        return self::$currentPic;
    }

    /**
     * @param QGPic $currentPic
     */
    public static function setCurrentPic ($currentPic)
    {
        self::$currentPic = $currentPic;
    }

}

class QGView
{

    private $title, $description, $pics;
    const ARG_TITLE          = 'title';
    const ARG_DESCRIPTION    = 'description';
    const PATT_MATCH_CONTENT = "/<a[^>]*?href=(?:'|\")(.*?)(?:'|\")[^>]*?><img[^>]*?src=(?:'|\")(.*?)(?:'|\")[^>]*?>.*?<\\/a>/";

    /**
     * @param string $title       Album title
     * @param string $description Album description
     * @param array  $pics        Pictures in album
     */
    function __construct ($title, $description, array $pics = array())
    {
        $this->title       = $title;
        $this->description = $description;
        $this->pics        = $pics;
    }


    /**
     * Parses shortcode content and returns QGView instance
     *
     * @param array  $args    shortcode args
     * @param string $content shortcode content
     *
     * @return QGView
     */
    public static function parse (array $args, $content)
    {
        $options = self::parseArgs($args);
        $pics    = self::parseContent($content);
        return new QGView($options[self::ARG_TITLE], $options[self::ARG_DESCRIPTION], $pics);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    private static function parseArgs (array $args)
    {
        return array_merge(array(
            self::ARG_TITLE       => '',
            self::ARG_DESCRIPTION => ''
        ), $args);
    }

    private static function parseContent ($content)
    {
        $pics = array();
        if (preg_match_all(self::PATT_MATCH_CONTENT, $content, $matches, PREG_SET_ORDER) !== false)
            foreach ($matches as $match) {
                // TODO improve performance on figuring out the sizes
                $sizes  = getimagesize(WP_CONTENT_DIR . str_replace(content_url(), '', $match[1]));
                $pics[] = new QGPic($match[2], $match[1], $sizes[0], $sizes[1], '', '');
            }
        return $pics;
    }

    /**
     * @return array
     */
    public function getPics ()
    {
        return $this->pics;
    }

    public function output ()
    {
        QG::setCurrentView($this);
        QG::getTemplate(QG::TEMPLATE_VIEW);
    }

}

class QGPic
{

    private $thumbnailUrl, $fullsizeUrl, $width, $height, $title, $description;

    /**
     * @param string $thumbnailUrl
     * @param string $fullsizeUrl
     * @param int    $width
     * @param int    $height
     * @param string $title
     * @param string $description
     */
    function __construct ($thumbnailUrl, $fullsizeUrl, $width, $height, $title, $description)
    {
        $this->thumbnailUrl = $thumbnailUrl;
        $this->fullsizeUrl  = $fullsizeUrl;
        $this->width        = $width;
        $this->height       = $height;
        $this->title        = $title;
        $this->description  = $description;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl ()
    {
        return $this->thumbnailUrl;
    }

    /**
     * @return string
     */
    public function getFullsizeUrl ()
    {
        return $this->fullsizeUrl;
    }

    /**
     * @return int
     */
    public function getWidth ()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight ()
    {
        return $this->height;
    }

    /**
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription ()
    {
        return $this->description;
    }

}

add_shortcode('quickgallery', function ($args, $content, $tag) {

    if ($args === '')
        $args = array();

    $view = QGView::parse($args, $content);

    ob_start();
    $view->output();
    return ob_get_clean();

});

add_action('wp_enqueue_scripts', function () {
    wp_register_script('underscore', plugins_url('bower_components/underscore/underscore-min.js'));
    wp_enqueue_script('quickgallery', plugins_url('dist/js/quickgallery.js', __FILE__), array('jquery', 'underscore'), false, true);
    wp_enqueue_style('photoswipe', plugins_url('bower_components/photoswipe/dist/photoswipe.css', __FILE__));
    wp_enqueue_style('photoswipe-default-skin', plugins_url('bower_components/photoswipe/dist/default-skin/default-skin.css', __FILE__));
    wp_enqueue_style('quickgallery', plugins_url('dist/css/quickgallery.css', __FILE__));
});

add_action('wp_footer', function () {
    QG::getTemplate(QG::TEMPLATE_FOOTER);
});