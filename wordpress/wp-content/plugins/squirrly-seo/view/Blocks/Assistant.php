<?php defined('ABSPATH') || die('Cheatin\' uh?'); ?>
<?php $page = apply_filters('sq_page', SQ_Classes_Helpers_Tools::getValue('page', '')); ?>
    <?php if ($page == 'sq_research') { ?>
        <div class="card-title text-center text-black-50 mt-3"><?php echo esc_html__('Keyword Research Mastery Tasks','squirrly-seo') ?></div>
    <?php }elseif ($page == 'sq_assistant') { ?>
        <div class="card-title text-center text-black-50 mt-3"><?php echo esc_html__('Live Assistant Mastery Tasks','squirrly-seo') ?></div>
    <?php }elseif ($page == 'sq_automation') { ?>
        <div class="card-title text-center text-black-50 mt-3"><?php echo esc_html__('Automation Mastery Tasks','squirrly-seo') ?></div>
    <?php }elseif ($page == 'sq_seosettings') { ?>
        <div class="card-title text-center text-black-50 mt-3"><?php echo esc_html__('SEO Configuration Mastery Tasks','squirrly-seo') ?></div>
    <?php }elseif ($page == 'sq_audits') { ?>
        <div class="card-title text-center text-black-50 mt-3"><?php echo esc_html__('Audit Mastery Tasks','squirrly-seo') ?></div>
    <?php }elseif ($page == 'sq_rankings') { ?>
        <div class="card-title text-center text-black-50 mt-3"><?php echo esc_html__('Rankings Mastery Tasks','squirrly-seo') ?></div>
    <?php }?>
    <div id="sq_assistant_<?php echo esc_attr($page) ?>" class="sq_assistant">
        <?php echo SQ_Classes_ObjController::getClass('SQ_Models_Assistant')->getAssistant($page); ?>
    </div>

