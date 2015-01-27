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

    /**
     * Some predefined aspect ratios from which the one occurring most frequently will be picked and used to crop the
     * images in the album if $cropImages is set to true
     * @var array
     */
    private static $aspectRatios = array('3x2', '4x3', '1x1', '3x4');

    private $title;

    private $description;

    private $pics;

    private $meanAspectRatio = null;

    const ARG_TITLE          = 'title';
    const ARG_DESCRIPTION    = 'description';
    const ARG_CROP_IMAGES        = 'crop_images';

    const PATT_MATCH_CONTENT = "/<a[^>]*?href=(?:'|\")(.*?)(?:'|\")[^>]*?><img[^>]*?src=(?:'|\")(.*?)(?:'|\")[^>]*?>.*?<\\/a>/";
    const PATT_MATCH_ASPECTRATIO = "/(\\d+)x(\\d+)/";

    /**
     * @param string  $title       Album title
     * @param string  $description Album description
     * @param boolean $cropImages  Crop the images to the most frequently occurring aspect ratio
     * @param array   $pics        Pictures in album
     */
    function __construct ($title, $description, $cropImages, array $pics = array())
    {
        $this->title       = $title;
        $this->description = $description;
        $this->pics        = $pics;
        if ($cropImages)
            $this->meanAspectRatio = self::figureOutMeanAspectRatio($this->pics, self::$aspectRatios);
    }

    private static function figureOutMeanAspectRatio (array $pics, array $aspects)
    {
        $parsedRatios = array();
        $countRatios  = array();
        foreach ($aspects as $aspect)
            if (preg_match(self::PATT_MATCH_ASPECTRATIO, $aspect, $matches) === 1) {
                $parsedRatios[$aspect] = $matches[1] / $matches[2];
                $countRatios[$aspect]  = 0;
            }
        foreach ($pics as $pic)
            $countRatios[array_search(self::figureOutAspectRatio($pic, array_values($parsedRatios)), $parsedRatios)]++;
        return array_search(max($countRatios), $countRatios);
    }

    /**
     * From the aspect ratios present in $ratios, picks the one closest matching the dimensions of $pic
     *
     * @param QGPic $pic
     * @param array $ratios
     *
     * @return int|float
     */
    private static function figureOutAspectRatio (QGPic $pic, array $ratios)
    {
        $minDiff  = 999;
        $minRatio = 1;
        foreach ($ratios as $ratio)
            if (($diff = abs($ratio - $pic->getAspectRatio())) < $minDiff) {
                $minDiff  = $diff;
                $minRatio = $ratio;
            }
        return $minRatio;
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

        return new QGView(
            $options[self::ARG_TITLE],
            $options[self::ARG_DESCRIPTION],
            $options[self::ARG_CROP_IMAGES],
            $pics);
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
            self::ARG_DESCRIPTION => '',
            self::ARG_CROP_IMAGES => false,
        ), $args);
    }

    private static function parseContent ($content)
    {
        $pics = array();
        if (preg_match_all(self::PATT_MATCH_CONTENT, $content, $matches, PREG_SET_ORDER) !== false)
            foreach ($matches as $match) {
                // TODO improve performance on figuring out the sizes. Perhaps use some form of caching
                $imageUrl = $match[1];
                $sizes    = getimagesize(self::getPathByUrl($imageUrl));
                $pics[]   = new QGPic($match[2], $match[1], $sizes[0], $sizes[1], '', '');
            }
        return $pics;
    }

    /**
     * Gets the most common aspect ratio amongst the pictures in the album, or -1 if it has not been
     * determined
     * @return float
     */
    public function getMeanAspectRatio ()
    {
        if ($this->meanAspectRatio !== null && preg_match(self::PATT_MATCH_ASPECTRATIO, $this->meanAspectRatio, $matches) === 1)
            return $matches[1] / $matches[2];
        return -1;
    }

    /**
     * Resolves URL to a path accessible through the local file system
     *
     * @param $imageUrl
     *
     * @return string
     */
    private static function getPathByUrl ($imageUrl)
    {
        $contentUrl = content_url();
        if (strpos($imageUrl, '/') === 0)
            $imageUrl = self::getRootUrl() . $imageUrl;
        return WP_CONTENT_DIR . str_replace($contentUrl, '', $imageUrl);
    }

    /**
     * @return string
     */
    private static function getRootUrl ()
    {
        if (defined('WP_HOME_ROOT'))
            return WP_HOME_ROOT;
        else
            return home_url();
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

    /**
     * @return float
     */
    public function getAspectRatio ()
    {
        return $this->width / $this->height;
    }

}

add_shortcode('quickgallery', function ($args, $content, $tag) {

    if ($args === '')
        $args = array();

    $view = QGView::parse(apply_filters('qg_args', $args), $content);

    ob_start();
    $view->output();
    return ob_get_clean();

});

add_action('wp_enqueue_scripts', function () {
    wp_register_script('underscore', plugins_url('dist/js/lib/underscore/underscore-min.js'));
    wp_enqueue_script('quickgallery', plugins_url('dist/js/quickgallery.js', __FILE__), array('jquery', 'underscore'), false, true);
    wp_enqueue_style('quickgallery', plugins_url('dist/css/qg.css', __FILE__));
});

add_action('wp_footer', function () {
    QG::getTemplate(QG::TEMPLATE_FOOTER);
});