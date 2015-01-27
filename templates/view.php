<div class="quickgallery" data-mean-aspectratio="<?php echo QG::getCurrentView()->getMeanAspectRatio(); ?>" itemscope
     itemtype="http://schema.org/ImageGallery">

    <?php

    foreach(QG::getCurrentView()->getPics() as $pic) {
        QG::setCurrentPic($pic);
        QG::getTemplate(QG::TEMPLATE_PIC);
    }

    ?>

</div>