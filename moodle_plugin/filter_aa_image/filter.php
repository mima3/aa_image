<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Main filter class
 *
 * @package   filter_aa_image
 * @author    todo
 * @copyright todo
 * @license   todo
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Filter class for aa_image code syntax.
 *
 * @package   filter_aa_image
 * @author    todo
 * @copyright todo
 * @license   todo
 */
class filter_aa_image extends moodle_text_filter {

    /**
     * The filter function is required, but the text just passes through.
     *
     * @param string $text HTML to be processed.
     * @param array $options Options for filter.
     * @return string String containing processed HTML.
     */
    public function filter($text, array $options = array()) {
        if (!is_string($text) || empty($text)) {
            return $text;
        }

        $re = "~\[aa_image\](.*?)\[/aa_image\]~isu";
        $result = preg_match_all($re, $text, $matches);
        if ($result > 0) {
            foreach ($matches[1] as $idx => $code) {
                $code = str_replace(
                    ['&gt;','&lt;','<pre>', '</pre>', '<p>', '</p>', '<br>', '&nbsp;'],
                    ['<', '>','', '', '', "", "\n", " "],
                    $code);
                $key = base64_encode(gzdeflate($code, 9));
                $newcode = '<p><img src="' . get_config('filter_aa_image', 'service_url') . '?d=' . urlencode($key). '"/></p>';
                                $text = str_replace($matches[0][$idx], $newcode, $text);
            }
        }

        return $text;
    }

    /**
     * Loads the javascript and style sheets.
     *
     * @param moodle_page $page The page we are going to add requirements to.
     * @param context $context The context which contents are going to be filtered.
     */
    public function setup($page, $context) {
        /*
        global $CFG;
        static $jsinitialised = false;

        if (empty($jsinitialised)) {
            
            $url = get_config('filter_aa_image', 'service_url');
            if ($cdn) {
                $css = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/' . $css . '.min.css';
            } else {
               $css =  $CFG->wwwroot . '/filter/syntaxhighlighter/styles/' . $css . '.min.css';
            }
            
            $styleurl = new moodle_url($css);

            $page->requires->js_call_amd('filter_syntaxhighlighter/hljs', 'initHighlighting');
            $page->requires->css($styleurl);

            $jsinitialised = true;
        }
        */
    }
}
