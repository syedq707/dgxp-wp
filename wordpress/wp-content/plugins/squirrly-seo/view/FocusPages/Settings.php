<?php defined('ABSPATH') || die('Cheatin\' uh?'); ?>
<div id="sq_wrap">
    <?php $view->show_view('Blocks/Toolbar'); ?>
    <?php do_action('sq_notices'); ?>
    <div id="sq_content" class="d-flex flex-row bg-white my-0 p-0 m-0">
        <?php
        if (!SQ_Classes_Helpers_Tools::userCan('sq_manage_focuspages')) {
            echo '<div class="col-12 alert alert-success text-center m-0 p-3">'. esc_html__("You do not have permission to access this page. You need Squirrly SEO Admin role.", 'squirrly-seo').'</div>';
            return;
        }
        ?>
        <?php $view->show_view('Blocks/Menu'); ?>
        <div class="d-flex flex-row flex-nowrap flex-grow-1 bg-light m-0 p-0">
            <div class="flex-grow-1 sq_flex m-0 py-0 px-4">
                <?php do_action('sq_form_notices'); ?>

                <div class="col-12 p-0 m-0">
                    <div class="p-2 rounded-top">
                        <div class="sq_icons_content p-3 py-4">
                            <div class="sq_icons sq_settings_icon m-2"></div>
                        </div>
                        <h3 class="card-title"><?php echo esc_html__("Focus Pages Settings", 'squirrly-seo'); ?></h3>
                    </div>
                    <div id="sq_focuspages" class="card col-12 p-0 tab-panel border-0">
                        <div class="p-0">
                            <div class="col-12 m-0 p-0">
                                <div class="card col-12 my-4 p-0 border-0 ">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sq_col sq_col_side ">
                <div class="col-12 p-0">
                    <?php echo SQ_Classes_ObjController::getClass('SQ_Core_BlockAssistant')->init(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
