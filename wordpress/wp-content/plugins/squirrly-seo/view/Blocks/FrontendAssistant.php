<?php defined('ABSPATH') || die('Cheatin\' uh?'); ?>
<?php if (SQ_Classes_Helpers_Tools::getOption('sq_api') <> '') { ?>
    <style>html{font-size: initial;}body ul.sq_notification,body ul.sq_complete{top: 0 !important;min-height: 40px;}</style>
    <div id="postsquirrly" class="sq_frontend sq_sticky" style="display: none">
        <?php $view->show_view('Blocks/LiveAssistant'); ?>
    </div>
<?php }?>