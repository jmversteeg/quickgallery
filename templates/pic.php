<?php
$pic = QG::getCurrentPic();
?>
<figure itemscope itemtype="http://schema.org/ImageObject">
    <a href="<?php echo $pic->getFullsizeUrl(); ?>" itemprop="contentUrl" data-size="<?php echo $pic->getWidth(); ?>x<?php echo $pic->getHeight(); ?>">
        <img src="<?php echo $pic->getThumbnailUrl(); ?>" itemprop="thumbnail" alt="<?php echo $pic->getTitle(); ?>" />
    </a>
    <figcaption itemprop="caption description"><?php echo $pic->getTitle(); ?></figcaption>
</figure>