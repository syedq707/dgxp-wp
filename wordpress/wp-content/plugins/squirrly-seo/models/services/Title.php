<?php

class SQ_Models_Services_Title extends SQ_Models_Abstract_Seo
{


    public function __construct()
    {
        parent::__construct();

        if (isset($this->_post->sq->doseo) && $this->_post->sq->doseo) {
            if (!$this->_post->sq->do_metas) {
                add_filter('sq_title', array($this, 'returnFalse'));
                return;
            }

            add_filter('sq_title', array($this, 'generateTitle'));
            add_filter('sq_title', array($this, 'clearTitle'), 98);
            add_filter('sq_title', array($this, 'packTitle'), 99);
        } else {
            add_filter('sq_title', array($this, 'returnFalse'));
        }

    }

    public function generateTitle($title)
    {
        //Compatibility with ACF
        if (SQ_Classes_Helpers_Tools::isPluginInstalled('advanced-custom-fields/acf.php')) {
            if (isset($this->_post->ID) && $this->_post->ID) {
                if ($_sq_custom = get_post_meta($this->_post->ID, '_sq_title', true)) {
                    return $_sq_custom;
                }
            }
        }

        if ($this->_post->sq->title <> '') {
            $title = $this->_post->sq->title;
        } else {
            $title = $this->_post->post_title = get_the_title();
        }

        return $title ;
    }

    public function packTitle($title = '')
    {
        if ($title <> '') {
            return sprintf("<title>%s</title>", $title);
        }

        return false;
    }

}
