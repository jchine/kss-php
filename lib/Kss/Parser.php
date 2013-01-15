<?php
/*
 * This file is a part of KSS-PHP
 *
 * (c) 2013 Jonathan Chiné
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kss;

use Kss\CommentParser;
use Kss\Section;

/**
* The Parser class.
*
* @author Jonathan Chiné <jonathan.chine@gmail.com>
*/
class Parser 
{

    protected $attr_sections = array();

    /**
     * Initializes a new parser based on a directory of files. Scans
     * within the directory recursively for any comment blocks that look like
     * KSS.
     * 
     * @param  string $path Each path String where style files are located.
     */
    public function __construct($paths = null)
    {
        $file_paths = array();
        if (!is_null($paths)) {
            if (!is_array($paths)) {
                $file_paths[] = $paths;
            }
            else {
                $file_paths = $paths;
            }

            $this->attr_sections = array();

            foreach ($file_paths as $key => $path) {
                $pattern = $path."/*.*"; // "/**/*.*"
                foreach (glob($pattern) as $file) {
                    $parser = new CommentParser($file);
                    $blocks = $parser->blocks();  
                    foreach ($blocks as $key => $comment_block) {
                        if ($this->is_kss_block($comment_block)) {
                            $this->add_section($comment_block, $file);
                        }
                    }  
                }
            }    
        }
    }

    /**
     * [add_section description]
     * @param string $comment_text [description]
     * @param string $file_name    [description]
     */
    public function add_section($comment_text='', $file_name='')
    {
        $base_name = basename($file_name);
        $section = new Section($comment_text, $base_name);
        $this->attr_sections[$section->section()] = $section;
    }

    /**
     * Takes a cleaned (no comment syntax like // or /*) comment
     * block and determines whether it is a KSS documentation block.
     * 
     * @param  string  $cleaned_comment 
     * @return boolean                  indicating whether the block conforms to KSS.
     */
    public function is_kss_block($cleaned_comment='')
    {
        if (is_string($cleaned_comment)) {
            $pattern = "/Styleguide \d/";
            $possible_reference = explode("\n\n", $cleaned_comment);
            return preg_match($pattern, end($possible_reference));
        }
        else {
            return false;
        }
    }

    /**
     * Finds the Section for a given styleguide reference.
     * 
     * @param  string $reference Section reference.
     * @return Section           Section for a reference, or a blank Section if none found.
     */
    public function section($reference='')
    {
        return (isset($this->attr_sections[$reference])) ? $this->attr_sections[$reference] : new Section();
    }
}