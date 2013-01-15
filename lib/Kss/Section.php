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

use Kss\Modifier;

/**
* The Section class.
* Represents a styleguide section. Each section describes one UI
* element. A Section can be thought of as the collection of the description,
* modifiers, and styleguide reference.
* 
* @author Jonathan Chiné <jonathan.chine@gmail.com>
*/
class Section 
{
    // the raw comment text for the section, not including comment
    // syntax (such as // or /*).
    protected $attr_raw= '';

    // the filename where this section is found.
    protected $attr_filename= '';

    //protected $comment_sections= array();
    //protected $section = '';

    /**
     * Public: Initialize a new Section
     * 
     * @param string $comment_text The raw comment String, minus any comment syntax.
     * @param string $filename     The filename as a String.
     */
    function __construct($comment_text='', $filename='')
    {
        $this->attr_raw = $comment_text;
        $this->attr_filename = $filename;
    }

    /**
     * Public: Returns the raw comment text for the section, not including comment
     * 
     * @return string 
     */
    public function raw()
    {
        return $this->attr_raw;
    }

    /**
     * Public: Returns the filename where this section is found.
     * 
     * @return string
     */
    public function filename()
    {
        return $this->attr_filename;
    }

    /**
     * Public: Splits up the raw comment text into comment sections that represent
     *         description, modifiers, etc.
     * 
     * @return array Array of comment Strings.
     */
    public function comment_sections()
    {
        /*
        if (count($this->comment_sections) > 0) {
            return $this->comment_sections;
        }
        else {
            $this->comment_sections = ($this->attr_raw != '') ? explode("\n",$this->attr_raw) : array();
            return $this->comment_sections;
        }
        */
        return ($this->attr_raw != '') ? explode("\n\n",$this->attr_raw) : array();
    }

    /**
     * Public: The styleguide section for which this comment block references.
     * 
     * @return string The section reference String (ex: "2.1.8").
     */
    public function section()
    {
        /*if ($this->section != '') {
            return $this->section;
        }
        else {*/
            // Kill trailing period
            $pattern = "/\.$/";     // remove last "."
            $cleaned = $this->section_comment();
            $cleaned = trim($cleaned);
            $cleaned = preg_replace($pattern, '', $cleaned);

            $pattern = "/Styleguide (.+)/";
            preg_match_all($pattern, $cleaned, $sections);
            //$this->section = $sections[1][0];
            //return $this->section;
            return $sections[1][0];
        //}
    }

    /**
     * Public: The description section of a styleguide comment block.
     * 
     * @return string the description String.
     */
    public function description()
    {
        $sections = array();
        // Aux values
        $comment_sections = $this->comment_sections();
        $section_comment = $this->section_comment(); 
        $modifiers_comment = $this->modifiers_comment();
        
        foreach ( $comment_sections as $key => $section) {
            if ( ($section != $section_comment) && ($section != $modifiers_comment) ) {
                $sections[] = $section;
            }
        }
        return trim(implode("\n\n", $sections));
    }

    /**
     * Public: The modifiers section of a styleguide comment block.
     * 
     * @return array Array of Modifiers.
     */
    public function modifiers()
    {
        $last_indent = 0;
        $modifiers = array();

        if (!$this->modifiers_comment()) {
            return $modifiers;
        }
        else {
            $text_modifier = $this->modifiers_comment();
            $pattern = "/^\s*/";
            $paragraphs = explode("\n", $text_modifier);
            foreach ( $paragraphs as $key => $line) {
                if (!empty($line) || (trim($line) != "")) {
                    preg_match($pattern, $line, $text);
                    $indent = strlen($text[0]);

                    if (($indent > $last_indent)) {
                        $last = end($modifiers);
                        $last->description(" ");
                    }
                    else {
                        $arrLine = explode(" - ", $line);
                        if (count($arrLine) == 2) {
                            $modifiers[] = new Modifier(trim($arrLine[0]),trim($arrLine[1]));
                        }
                    }
                }
            } // end foreach
        }
        return $modifiers;
    }

    /**
     * Private: Search the first Styleguide section (ex: "Styleguide 2.1.8").
     * 
     * @return string Section title 
     */
    private function section_comment()
    {
        $pattern = "/Styleguide \d/i"; // /i for case insensitive
        $text_section = '';
        $values_comment_sections = array_values($this->comment_sections());
        foreach ( $values_comment_sections as $key => $text) {
            if (preg_match($pattern, $text)) {
                $text_section = $text;
                break;
            }
        }

        return $text_section;
    }

    /**
     * Private: Search modifiers comment, is the last comment
     * 
     * @return Section Last Comment Section 
     */
    private function modifiers_comment()
    {
        $sections = array();
        $text_section_comment = $this->section_comment();
        $comment_sections = $this->comment_sections();
        foreach ($comment_sections as $key => $section) {
            if ( ($key > 0) && ($section != $text_section_comment) ) {
                $sections[] = $section; 
            }
        }
        return end($sections); // return false if is empty array.
    }
}