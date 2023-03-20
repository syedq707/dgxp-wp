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

                <div class="sq_breadcrumbs my-4"><?php echo SQ_Classes_ObjController::getClass('SQ_Models_Menu')->getBreadcrumbs('labels') ?></div>
                <h3 class="mt-4 card-title">
                    <?php echo esc_html__("Briefcase Labels", 'squirrly-seo'); ?>
                    <div class="sq_help_question d-inline">
                        <a href="https://howto12.squirrly.co/kb/keyword-research-and-seo-strategy/#labels" target="_blank"><i class="fa-solid fa-question-circle"></i></a>
                    </div>
                </h3>
                <div class="col-7 small m-0 p-0">
                    <?php echo esc_html__("Briefcase Labels will help you sort your keywords based on your SEO strategy. Labels are like categories and you can quickly filter your keywords by one or more labels.", 'squirrly-seo'); ?>
                </div>

                <div id="sq_briefcaselabels" class="col-12 m-0 p-0 border-0">
                    <?php do_action('sq_subscription_notices'); ?>

                    <div class="sq_add_labels_dialog modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content bg-white rounded-0">
                                <div class="modal-header">
                                    <h4 class="modal-title"><?php echo esc_html__("Add New Label", 'squirrly-seo'); ?></h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="sq_labelname"><?php echo esc_html__("Label Name", 'squirrly-seo'); ?></label>
                                        <input type="text" class="form-control" id="sq_labelname" maxlength="35"/>
                                    </div>
                                    <div class="form-group">
                                        <div><label for="sq_labelcolor" style="display: block"><?php echo esc_html__("Label Color", 'squirrly-seo'); ?></label></div>
                                        <input type="text" id="sq_labelcolor" value="<?php echo sprintf('#%06X', mt_rand(0, 0xFFFFFF)); ?>"/>
                                    </div>
                                </div>
                                <div class="modal-footer" style="border-bottom: 1px solid #ddd;">
                                    <button type="button" id="sq_save_label" class="btn btn-primary"><?php echo esc_html__("Add Label", 'squirrly-seo'); ?></button>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="sq_edit_label_dialog modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content bg-white rounded-0">
                                <div class="modal-header">
                                    <h4 class="modal-title"><?php echo esc_html__("Edit Label", 'squirrly-seo'); ?></h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="sq_labelname"><?php echo esc_html__("Label Name", 'squirrly-seo'); ?></label>
                                        <input type="text" class="form-control" id="sq_labelname" maxlength="35"/>
                                    </div>
                                    <div class="form-group">
                                        <div><label for="sq_labelcolor"><?php echo esc_html__("Label Color", 'squirrly-seo'); ?></label></div>
                                        <input type="text" id="sq_labelcolor"/>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="hidden" id="sq_labelid"/>
                                    <button type="button" id="sq_save_label" class="btn btn-primary"><?php echo esc_html__("Save Label", 'squirrly-seo'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 m-0 p-0 my-5">
                        <?php if (is_array($view->labels) && !empty($view->labels)) { ?>
                            <div class="row col-12 m-0 p-0 py-3">
                                <div class="col-6 p-0">
                                    <select name="sq_bulk_action" class="sq_bulk_action" >
                                        <option value=""><?php echo esc_html__("Bulk Actions", 'squirrly-seo') ?></option>
                                        <option value="sq_ajax_labels_bulk_delete" data-confirm="<?php echo esc_html__("Ar you sure you want to delete the labels?", 'squirrly-seo') ?>"><?php echo esc_html__("Delete") ?></option>
                                    </select>
                                    <button class="sq_bulk_submit btn btn-primary"><?php echo esc_html__("Apply"); ?></button>
                                </div>

                                <div class="col-6 p-0 text-right">
                                    <button class="btn btn-link text-primary" onclick="jQuery('.sq_add_labels_dialog').modal('show')" data-dismiss="modal">
                                        <?php echo esc_html__("Add new Label", 'squirrly-seo'); ?> <i class="fa-solid fa-plus-square"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-0">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th style="width: 10px;"><input type="checkbox" class="sq_bulk_select_input" /></th>
                                        <th style="width: 70%;"><?php echo esc_html__("Name", 'squirrly-seo') ?></th>
                                        <th scope="col" title="<?php echo esc_html__("Color", 'squirrly-seo') ?>"><?php echo esc_html__("Color", 'squirrly-seo') ?></th>
                                        <th style="width: 20px;"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($view->labels as $key => $row) {
                                        if(!$row->id) continue;
                                        ?>
                                        <tr id="sq_row_<?php echo (int)$row->id ?>">
                                            <td style="width: 10px;">
                                                <?php if (SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) { ?>
                                                    <input type="checkbox" name="sq_edit[]" class="sq_bulk_input" value="<?php echo (int)$row->id ?>"/>
                                                <?php } ?>
                                            </td>
                                            <td style="width: 50%;" class="text-left">
                                                <?php echo esc_html($row->name) ?>
                                            </td>
                                            <td style="width: 50%;">
                                                <span style="display: inline-block; background-color:<?php echo esc_attr($row->color) ?>; width: 100px;height: 20px; margin-right: 10px;"></span>
                                            </td>

                                            <td class="px-0 py-2" style="width: 20px">
                                                <div class="sq_sm_menu">
                                                    <div class="sm_icon_button sm_icon_options">
                                                        <i class="fa-solid fa-ellipsis-v"></i>
                                                    </div>
                                                    <div class="sq_sm_dropdown">
                                                        <ul class="text-left p-2 m-0">
                                                            <li class="sq_edit_label m-0 p-1 py-2" data-id="<?php echo (int)$row->id ?>" data-name="<?php echo esc_attr($row->name) ?>" data-color="<?php echo esc_attr($row->color) ?>">
                                                                <i class="sq_icons_small fa-solid fa-tag"></i>
                                                                <?php echo esc_html__("Edit Label", 'squirrly-seo') ?>
                                                            </li>
                                                            <?php if (SQ_Classes_Helpers_Tools::userCan('sq_manage_settings')) { ?>
                                                                <li class="sq_delete_label m-0 p-1 py-2" data-id="<?php echo (int)$row->id ?>">
                                                                    <i class="sq_icons_small fa-solid fa-trash"></i>
                                                                    <?php echo esc_html__("Delete Label", 'squirrly-seo') ?>
                                                                </li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="my-5">
                                <h4 class="text-center"><?php echo esc_html__("Welcome to Briefcase Labels", 'squirrly-seo'); ?></h4>
                                <div class="col-12 m-2 text-center">
                                    <button class="btn btn-lg btn-primary" onclick="jQuery('.sq_add_labels_dialog').modal('show')" data-dismiss="modal"><i class="fa-solid fa-plus-square-o"></i> <?php echo esc_html__("Add label to organize the keywords in Briefcase", 'squirrly-seo'); ?></button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                </div>

                <div class="sq_tips col-12 m-0 p-0">
                    <h5 class="text-left my-3 font-weight-bold"><i class="fa-solid fa-exclamation-circle" ></i> <?php echo esc_html__("Tips and Tricks", 'squirrly-seo'); ?></h5>
                    <ul class="mx-4">
                        <li class="text-left">
                            <?php echo esc_html__("To delete Labels in bulk: select the labels you want to delete, go to Bulk Actions, select Delete, and then click on Apply.", 'squirrly-seo'); ?>
                        </li>
                        <li class="text-left">
                            <?php echo esc_html__("NOTE! Deleting a Label will NOT delete the keywords assigned to it from Briefcase.", 'squirrly-seo'); ?>
                        </li>
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
