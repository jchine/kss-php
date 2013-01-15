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

/**
* The Kss class.
*
* @author Jonathan Chiné <jonathan.chine@gmail.com>
*/
class CommentParser 
{
    protected $options = array();
    protected $file_path = '';
    protected $blocks = array();
    protected $parsed = false;

    /**
     * Initializes a new comment parser object. Does not parse on initialization.
     * 
     * @param  string $file_path The location of the file to parse as a String.
     * @param  array  $options   Optional options array.
     * @return [type]            [description]
     */
    public function __construct($file_path='', array $options = array())
    {
        $this->options = $options;
        $this->file_path = $file_path;

        if (isset($options['preserve_whitespace'])) {
            $this->options['preserve_whitespace'] = $options['preserve_whitespace'];
        }
        else {
            $this->options['preserve_whitespace'] = false;
        }
    }

    /**
     * Is this a sinlge-line comment? // This style
     * 
     * @param  string  $line A String of one line of text.
     * @return boolean       
     */
    public function is_single_line_comment($line='')
    {
        $pattern = "/^\s*\/\//";
        return (preg_match($pattern, $line));
    }

    /**
     *  Is this the start of a multi-line comment? /* This style
     * 
     * @param  string  $line A String of one line of text.
     * @return boolean       
     */
    public function is_start_multi_line_comment($line='')
    {
        $pattern = "/^\s*\/\*/";
        return (preg_match($pattern, $line));
    }

    /**
     * Is this the end of a multi-line comment? /* This style 
     * 
     * @param  string  $line A String of one line of text.
     * @return boolean       
     */
    public function is_end_multi_line_comment($line='')
    {
        $pattern = "/.*\*\//";
        return ($this->is_single_line_comment($line)) ? false: (preg_match($pattern, $line));   
    }

    /**
     * Removes comment identifiers for single-line comments.
     * 
     * @param  string $line A String of one line of text.
     * @return string       
     */
    public function parse_single_line($line='')
    {
        $pattern = "/\s*\/\//";
        $line = preg_replace($pattern, '', $line);
        $line = rtrim($line);
        return $line;
    }

    /**
     * Remove comment identifiers for multi-line comments.
     * 
     * @param  string $line A String of one line of text.
     * @return string       
     */
    public function parse_multi_line($line='')
    {
        $pattern_star = "/\s*\/\*/";
        $pattern_end  = "/\*\//";
        $line = preg_replace($pattern_star, '', $line);
        $line = preg_replace($pattern_end, '', $line);
        $line = rtrim($line);
        return $line;   
    }

    /**
     * The different sections of parsed comment text. A section is
     * either a multi-line comment block's content, or consecutive lines of
     * single-line comments
     * 
     * @return Array of parsed comment Strings.
     */
    public function blocks()
    {
        if (!$this->parsed) {
            $this->parse_blocks();
        }
        return $this->blocks;
    }

    /**
     * Parse the file for comment blocks and populate them into blocks.
     * 
     * @return Array of parsed comment Strings.
     */
    public function parse_blocks()
    {
        $files = array();
        $pattern = '';
        $current_block = '';
        $inside_single_line_block = false;
        $inside_multi_line_block = false;

        if (is_dir($this->file_path)) {
            $pattern = (substr($this->file_path, -1) == '/') ? $this->file_path. '*' : $this->file_path . '/*';
            foreach (glob($pattern) as $file) {
                $files[] = $file;
            }
        }
        else if (is_file($this->file_path)) {
            $files[] = $this->file_path;
        }

        foreach ($files as $key => $file) {
            $current_block = '';
            $inside_single_line_block = false;
            $inside_multi_line_block = false;

            $lines = $this->loadFile($file);
            foreach ( $lines as $key => $line) {
                // Parse single-line style
                if ($this->is_single_line_comment($line)) {
                    $parsed = $this->parse_single_line($line);
                    if ($inside_single_line_block) {
                        //$current_block .= "\n#{" . $parsed . "}";
                        $current_block .= "\n" . $parsed . "";
                    }
                    else {
                        $current_block .= $parsed;
                        $inside_single_line_block = true;
                    }
                }

                // Parse multi-lines style
                if ($this->is_start_multi_line_comment($line) || $inside_multi_line_block) {
                    $parsed = $this->parse_multi_line($line);
                    if ($inside_multi_line_block) {
                        //$current_block .= "\n#{" . $parsed . "}";
                        $current_block .= "\n" . $parsed . "";
                    }
                    else {
                        $current_block .= $parsed;
                        $inside_multi_line_block = true;
                    }
                }

                // End a multi-line block if detected
                //$inside_multi_line_block = ($this->is_end_multi_line_comment($line)) ? $this->is_end_multi_line_comment($line) : false;
                if ($this->is_end_multi_line_comment($line)) {
                    $inside_multi_line_block = false;    
                }

                // Store the current block if we're done
                //if ( ($this->is_single_line_comment($line) || $inside_multi_line_block) ) {
                if ( ($inside_single_line_block || !$inside_multi_line_block) ) {
                    if (strlen($current_block) > 0) $this->blocks[] = $current_block;

                    $inside_single_line_block = false;
                    $current_block = '';
                }
            } // end foreach $lines
        }// end foreach $files
        $this->parsed = true;
        //return $this->blocks;
    }

    /**
     * Loading content file and save to array, line to line
     * 
     * @param  string $file File name
     * @return array        of lines
     */
    public function loadFile($file='')
    {
        $lines = array();
        if ( !($lines = file($file)) ) {
            $fh = fopen($file,"r");
                do {
                    $temp = trim(fgets($fh)); // read line to line
                    // Delete UTF-8 Character BOM from line text
                    if (substr($temp,0,3) == pack("CCC",0xef,0xbb,0xbf)) $line = substr($temp,3); 
                    else $line = $temp;

                    $lines[] = $line;
                } while (!feof($fh)); 
                fclose($fh); 
        }
        return $lines;
    }

    /**
     * Normalizes the comment block to ignore any consistent preceding
     * whitespace. Consistent means the same amount of whitespace on every line
     * of the comment block. Also strips any whitespace at the start and end of
     * the whole block.
     * 
     * @param  string $text_block [description]
     * @return string             String of normalized text
     */
    public function normalize($text_block='')
    {
        $pattern = '';

        if ($this->options['preserve_whitespace']) return $text_block;

        // Strip out any preceding [whitespace]* that occur on every line. Not
        // the smartest, but I wonder if I care.
        $pattern = "/^(\s*\*+)/";
        $text_block = preg_match_all($pattern, $text_block, '');

        // Strip consistent indenting by measuring first line's whitespace
        $indent_size = 0;
        $pattern = "/^\s+/";    // This style:    hello   wolrd
        $unindented = explode("\n", $text_block);
        foreach ( $unindented as $key => $line) {
            $line = preg_replace($pattern, ' ', $line);
            $unindented[$key] = trim($line);
        }
        return trim(implode('\n', $unindented));
    }
}