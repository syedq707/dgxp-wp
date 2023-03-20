<?php defined('ABSPATH') || die('Cheatin\' uh?'); ?>
<?php
$refresh = false;
$report_time = SQ_Classes_Helpers_Tools::getOption('seoreport_time');
if (empty($report_time) || (time() - (int)$report_time) > (3600 * 12)) {
    $refresh = true;
}

$category_name = apply_filters('sq_page', SQ_Classes_Helpers_Tools::getValue('page', 'sq_dashboard'));

$ignored_count = $countdone = 0;
foreach ($view->report as $function => $row) {
    if (in_array($view->report[$function]['status'], array('completed', 'done', 'ignore'))) {
        $countdone++;

        if ($row['status'] == 'ignore') {
            $ignored_count++;
        }
    }
}

//Show all done image if all tasks are done
if ($countdone == count($view->report)) {
    $view->report = array();
}
?>
<div class="p-0 float-right" style="margin-top:-30px">
    <?php if (SQ_Classes_Helpers_Tools::userCan('sq_manage_snippets')) { ?>
        <button type="button" class="btn btn-primary btn-sm m-2 py-2 px-5 center-block sq_seocheck_submit" onclick="jQuery('#sq_loading_modal').modal();" <?php echo($refresh ? 'data-action="trigger"' : '') ?> >
            <?php echo esc_html__("Run SEO Test", 'squirrly-seo') ?> >>
        </button>
    <?php } ?>
</div>

<a name="tasks"></a>
<div class="sq_separator mt-4"></div>

<?php if (isset($view->report) && !empty($view->report)) { ?>
    <div id="sq_seocheck_tasks" class="col-12 my-0 py-2 px-0 col-12 border-0 shadow-none">
        <table class="table my-0">
            <tbody>
            <?php foreach ($view->report as $function => $row) {
                if (in_array($row['status'], array('done', 'ignore'))) {
                    continue;
                }
                ?>
                <tr>
                    <td class="p-3 bg-white" <?php echo($row['completed'] ? 'colspan="3" style="position:relative;"' : '') ?>>
                        <?php echo($row['completed'] ? '<div class="completed">' . esc_html__("Goal completed. Good Job!", 'squirrly-seo') . '</div>' : '') ?>
                        <h4 class="sq_seocheck_tasks_title text-left"><?php echo(isset($row['warning']) ? wp_kses_post($row['warning']) : '') ?></h4>
                        <?php if (isset($row['goal'])) { ?>
                            <div class="my-2 text-left">
                                <?php echo wp_kses_post($row['goal']) ?>
                            </div>
                        <?php } ?>
                        <div class="row p-0 m-0 flex-nowrap">
                            <?php if (isset($row['tools']) && !empty($row['tools'])) { ?>
                                <div class="small text-black-50 my-2 mr-2">
                                    <i class="sq_logo"></i> <?php echo esc_html__("use", 'squirrly-seo'); ?>:
                                    <?php echo wp_kses_post(join(', ', $row['tools']) )?>
                                </div>
                            <?php } ?>

                            <?php if (isset($row['time']) && (int)$row['time'] > 0) { ?>
                                <div class="small text-black-50 my-2 mr-2">
                                    <i class="dashicons dashicons-clock"  title="<?php echo esc_html__("Time to complete this goal.", 'squirrly-seo'); ?>"></i> <?php echo esc_html__("up to", 'squirrly-seo'); ?>:
                                    <span><?php echo(((int)$row['time'] < 60) ? (int)$row['time'] . ' seconds' : (((int)$row['time'] < 3600) ? ceil((int)$row['time'] / 60) . ' minutes' : ((int)$row['time'] / 3600) . ' hours')) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </td>
                    <?php if (!$row['completed']) { ?>
                        <td class="p-0 p-1 pr-0 bg-white" style="width: 150px; vertical-align: middle;">
                            <div class="text-right mx-1">
                                <?php if (SQ_Classes_Helpers_Tools::userCan('sq_manage_snippets')) {
                                    $dbtasks = json_decode(get_option(SQ_TASKS), true);
                                    ?>
                                    <div class="col p-0 m-1 mx-1">
                                        <div class="col-12 m-0 p-0 sq_task" data-category="<?php echo esc_attr($category_name) ?>" data-active="1" data-name="<?php echo esc_attr($function) ?>" data-completed="<?php echo (int)$row['completed'] ?>">
                                            <button type="button" class="btn btn-sm btn-primary text-white p-1 px-2 m-0" style="width: 130px" data-dismiss="modal">
                                                <?php echo esc_html__("Show me how", 'squirrly-seo') ?>
                                            </button>
                                            <?php if (isset($row['reopened']) && $row['reopened']) { ?>
                                                <div class="m-1 small">
                                                    <i class="fa-solid fa-warning text-danger"></i> <?php echo esc_html__("Goal is not done!", 'squirrly-seo') ?>
                                                </div>
                                            <?php } ?>
                                            <div class="title" style="display: none">
                                                <?php echo(isset($row['warning']) ? wp_kses_post($row['warning']) : '') ?>
                                            </div>
                                            <div class="description" style="display: none">
                                                <div class="row">
                                                    <div class="col py-1">

                                                        <div class="sq_seocheck_tasks_description p-1 py-3 m-0">
                                                            <?php echo(isset($row['message']) ? wp_kses_post($row['message']) : '') ?>

                                                            <?php if (isset($row['solution']) && $row['solution'] <> '') { ?>
                                                                <div class="sq_seocheck_tasks_solution my-3">
                                                                    <?php echo '<h5 class="text-primary font-weight-bold">' . esc_html__("SOLUTION", 'squirrly-seo') . ':</h5>' . wp_kses_post($row['solution']) ?>
                                                                </div>
                                                            <?php } ?>

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="row p-0 px-3 m-0">
                                                    <div class="col p-0 m-0">

                                                        <?php if (isset($row['link']) && isset($row['link'])) { ?>
                                                            <div class="float-right">
                                                                <a href="<?php echo esc_url($row['link']) ?>" target="_blank" class="btn btn-sm btn-primary text-white p-1 px-5 m-0">
                                                                    <?php echo esc_html__("Let's do this", 'squirrly-seo') ?>
                                                                </a>
                                                            </div>
                                                        <?php } ?>

                                                        <div class="float-right sq_save_ajax">
                                                            <input type="hidden" id="sq_done_<?php echo esc_attr($function) ?>" value="1">
                                                            <button type="button" class="btn btn-sm btn-link font-weight-bold p-1 px-5 mx-2" id="sq_done" data-input="sq_done_<?php echo esc_attr($function) ?>" data-name="<?php echo esc_attr($category_name) ?>|<?php echo esc_attr($function) ?>|done" data-action="sq_ajax_assistant" data-javascript="javascript:void(0);">
                                                                <?php echo esc_html__("Mark As Done", 'squirrly-seo') ?>
                                                            </button>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="message" style="display: none"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </td>
                        <td class="bg-white sq_save_ajax" style="width: 10px; vertical-align: middle;  padding-left: 0; padding-right: 0; margin: 0">
                            <?php if (SQ_Classes_Helpers_Tools::userCan('sq_manage_snippets')) { ?>
                                <input type="hidden" id="sq_ignore_<?php echo esc_attr($function) ?>" value="0">
                                <button type="button" class="float-right btn btn-sm btn-link p-2 px-3 m-0" id="sq_ignore" data-input="sq_ignore_<?php echo esc_attr($function) ?>" data-name="<?php echo esc_attr($category_name) ?>|<?php echo esc_attr($function) ?>" data-action="sq_ajax_assistant" data-javascript="javascript:void(0);" data-confirm="<?php echo esc_html__("Do you want to ignore this goal?", 'squirrly-seo') ?>">
                                    <i class="fa-solid fa-close"></i>
                                </button>
                            <?php } ?>
                        </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td colspan="3" class="p-2 m-0 border-0"></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>
<?php } ?>


<div class="row p-0 mb-5"  style="max-width: 1200px;">
    <div class="col p-0 m-2 mx-0 text-left">
        <?php if ((int)$ignored_count > 0) { ?>
            <form method="post" class="p-0 m-0">
                <?php SQ_Classes_Helpers_Tools::setNonce('sq_resetignored', 'sq_nonce'); ?>
                <input type="hidden" name="action" value="sq_resetignored"/>
                <button type="submit" class="btn btn-link text-black-50 small p-2 px-3 m-0">
                    <?php echo esc_html__("Show hidden goals", 'squirrly-seo') ?>
                    <span class="rounded-circle p-1 px-2 text-white bg-danger  small"><?php echo (int)$ignored_count ?></span>
                </button>
            </form>
        <?php } ?>

    </div>

    <?php if (empty($view->report)) { ?>
        <div class="col p-0 m-3 mx-0 text-right" style="font-size: 16px">
            <?php echo esc_html__("Next goals on", 'squirrly-seo') ?>:
            <span><?php echo date(get_option('date_format'), strtotime('+1 day')) ?></span>
        </div>
        <div class="col p-0 m-2 mx-0 text-right">
            <div class="col">
                <form method="post" class="p-0 m-0">
                    <?php SQ_Classes_Helpers_Tools::setNonce('sq_moretasks', 'sq_nonce'); ?>
                    <input type="hidden" name="action" value="sq_moretasks"/>
                    <button type="submit" class="btn btn-warning m-0 py-2 px-5 font-bold p-2 px-3 m-0">
                        <?php echo esc_html__("Load more goals if exist", 'squirrly-seo') ?> >>
                    </button>
                </form>
            </div>
        </div>
    <?php } ?>

</div>

