<?php defined('ABSPATH') || die('Cheatin\' uh?'); ?>
<div id="sq_wrap">
    <?php $view->show_view('Blocks/Toolbar'); ?>
    <?php do_action('sq_notices'); ?>
    <div id="sq_content" class="d-flex flex-row bg-white my-0 p-0 m-0">
        <?php
        if (!apply_filters('sq_load_snippet', true) || !SQ_Classes_Helpers_Tools::userCan('sq_manage_snippet')) {
            echo '<div class="col-12 alert alert-success text-center m-0 p-3">' . esc_html__("You do not have permission to access this page. You need Squirrly SEO Editor role.", 'squirrly-seo') . '</div>';
            return;
        }
        ?>
        <?php $view->show_view('Blocks/Menu'); ?>
        <div class="d-flex flex-row flex-nowrap flex-grow-1 bg-light m-0 p-0">
            <div class="flex-grow-1 sq_flex m-0 py-0 px-4">
                <?php do_action('sq_form_notices'); ?>

                <div class="sq_breadcrumbs my-4"><?php echo SQ_Classes_ObjController::getClass('SQ_Models_Menu')->getBreadcrumbs('history') ?></div>
                <h3 class="mt-4 card-title">
                    <?php echo esc_html__("Research History", 'squirrly-seo'); ?>
                    <div class="sq_help_question d-inline">
                        <a href="https://howto12.squirrly.co/kb/keyword-research-and-seo-strategy/#history" target="_blank"><i class="fa-solid fa-question-circle"></i></a>
                    </div>
                </h3>
                <div class="col-7 small m-0 p-0">
                    <?php echo esc_html__("See the Keyword Researches you made in the last 30 days", 'squirrly-seo'); ?>
                </div>

                <div id="sq_history" class="col-12 p-0 m-0 my-5">
                    <?php do_action('sq_subscription_notices'); ?>

                    <?php if (is_array($view->kr) && !empty($view->kr)) { ?>
                        <table class="sq_krhistory_list table table-striped table-hover">
                            <thead>
                            <tr>
                                <th scope="col"><?php echo esc_html__("Keyword", 'squirrly-seo') ?></th>
                                <th scope="col" title="<?php echo esc_html__("Country", 'squirrly-seo') ?>"><?php echo esc_html__("Co", 'squirrly-seo') ?></th>
                                <th style="width: 160px;"><?php echo esc_html__("Date", 'squirrly-seo') ?></th>
                                <th style="width: 160px;"><?php echo esc_html__("Details", 'squirrly-seo') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($view->kr as $key => $kr) {
                                ?>
                                <tr>
                                    <td style="350px" class="sq_kr_keyword" title="<?php echo esc_attr($kr->keyword) ?>"><?php echo esc_html($kr->keyword) ?></td>
                                    <td style="90px"><?php echo esc_html($kr->country) ?></td>
                                    <td style="120px">
                                        <div data-datetime="<?php echo strtotime($kr->datetime) ?>"><?php echo date(get_option('date_format'), strtotime($kr->datetime)) ?></div>
                                    </td>
                                    <td style="20px">
                                        <button type="button" data-id="<?php echo (int)$kr->id ?>" data-destination="#history<?php echo (int)$kr->id ?>" class="sq_history_details btn btn-link text-primary btn-sm px-5"><?php echo esc_html__("Show All Keywords", 'squirrly-seo') ?></button>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <h4 class="text-center"><?php echo esc_html__("Welcome to Keyword Research History", 'squirrly-seo'); ?></h4>
                        <h5 class="text-center"><?php echo esc_html__("See your research results and compare them over time", 'squirrly-seo'); ?>:</h5>
                        <div class="col-12 my-4 text-center">
                            <a href="<?php echo SQ_Classes_Helpers_Tools::getAdminUrl('sq_research', 'research') ?>" class="btn btn-lg btn-primary">
                                <i class="fa-solid fa-plus-square-o"></i> <?php echo esc_html__("Go Find New Keywords", 'squirrly-seo'); ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <div class="sq_tips col-12 m-0 p-0">
                    <h5 class="text-left my-3 font-weight-bold"><i class="fa-solid fa-exclamation-circle" ></i> <?php echo esc_html__("Tips and Tricks", 'squirrly-seo'); ?></h5>
                    <ul class="mx-4">
                        <li class="text-left"><?php echo esc_html__("Re-discover keywords you may have forgotten to add to Briefcase when you did your keyword research.", 'squirrly-seo'); ?></li>
                        <li class="text-left"><?php echo esc_html__("Squirrly keeps all of the data about the keywords you’ve already researched.", 'squirrly-seo'); ?></li>
                        <li class="text-left"><?php echo esc_html__("Checking the Keyword Research evolution over time is very important. Keywords can trend differently from week to week and finding the right time to use them in your articles is key to your success.", 'squirrly-seo'); ?></li>
                    </ul>
                </div>

                <?php SQ_Classes_ObjController::getClass('SQ_Core_BlockKnowledgeBase')->init(); ?>

            </div>
            <div class="sq_col_side bg-white">
                <div class="col-12 m-0 p-0 sq_sticky">
                    <?php echo SQ_Classes_ObjController::getClass('SQ_Core_BlockAssistant')->init(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
