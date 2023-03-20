<?php defined('ABSPATH') || die('Cheatin\' uh?'); ?>
<div id="sq_wrap">
    <?php $view->show_view('Blocks/Toolbar'); ?>
    <?php do_action('sq_form_notices'); ?>
    <div class="d-flex flex-row bg-white my-0 p-0 m-0">
	    <?php
	    if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) {
		    echo '<div class="col-12 alert alert-success text-center m-0 p-3">' . esc_html__("You do not have permission to access this page. You need Squirrly SEO Admin role", 'squirrly-seo') . '</div>';
		    return;
	    }
	    ?>
        <?php $view->show_view('Blocks/Menu'); ?>

        <div class="sq_flex flex-grow-1 bg-light mx-0 py-0 pl-5">
            <div class="sq_breadcrumbs mt-5"><?php echo SQ_Classes_ObjController::getClass('SQ_Models_Menu')->getBreadcrumbs('sq_features') ?></div>
            <?php SQ_Classes_ObjController::getClass('SQ_Core_BlockFeatures')->init(); ?>
        </div>

    </div>
</div>
