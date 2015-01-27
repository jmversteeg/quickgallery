<?php
$pic = QG::getCurrentPic();
?>
<figure itemscope itemtype="http://schema.org/ImageObject" data-aspectratio="<?php echo $pic->getAspectRatio(); ?>">
    <a href="<?php echo $pic->getFullsizeUrl(); ?>" itemprop="contentUrl" data-size="<?php echo $pic->getWidth(); ?>x<?php echo $pic->getHeight(); ?>">
        <div style="background-image: url(<?php echo $pic->getThumbnailUrl(); ?>); " itemprop="thumbnail"></div>
    </a>
    <figcaption itemprop="caption description"><?php echo $pic->getTitle(); ?></figcaption>
</figure>