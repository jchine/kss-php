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
* The Modifier class.
*
* @author Jonathan Chiné <jonathan.chine@gmail.com>
*/
class Modifier
{
    protected $attr_name = '';
    protected $attr_description = ''; 

    /**
     * Initialize a new Modifier.
     * 
     * @param  string $name        The name String of the modifier
     * @param  string $description The description String of the modifier
     */
    public function __construct($name='', $description='')
    {
        $this->attr_name = trim($name);
        $this->attr_description = $description;
    }

    /**
     * Returns the modifier name String or Add value to the name string of the modifier.
     *
     * @param  string $value Add value to name
     * @return string 
     */
    public function name($value=null)
    {
        if (!is_null($value)) {
            $this->attr_name .= $value;
        }
        return $this->attr_name;
    }    

    /**
     * Returns the description String for a Modifier or Add value to the description string of the modifier.
     * 
     * @param  string $value Add value to description
     * @return string
     */
    public function description($value=null)
    {
        if (!is_null($value)) {
            $this->attr_description .= $value;
        }
        return $this->attr_description;
    }
    
    /**
     * Returns the pseudo-class String for a Modifier
     * 
     * @return string pseudo-class-name
     */
    public function class_name()
    {
        $class_name = str_replace('.', ' ', $this->attr_name);
        $class_name = str_replace(':', ' pseudo-class-', $class_name);
        $class_name = trim($class_name);

        return $class_name;
    }
}