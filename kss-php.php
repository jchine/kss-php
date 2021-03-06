<?php
/*
 * This file is a part of KSS-PHP
 *
 * (c) 2013 Jonathan Chiné
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Installed via Composer 
require_once __DIR__.'/vendor/autoload.php';  

$kss = new Kss\Parser(__DIR__."/test/fixtures/css");

// Object Kss\Section
$section = $kss->section('2.1.1'); 
// Description of section: Your standard form button.
echo "Description: " . $section->description() . "<br />";

// Object Kss\Modifier
$modifier = $section->modifiers();
// Name of first modifier: ':hover'
$first = reset($modifier);
echo "First modifier: " . $first->name() . "<br />";
// Pseudo Class of modifier: 'pseudo-class-hover'
echo "Pseudo class: " . $first->class_name() . "<br />";
// Description of modifier: 
echo "Description modifier: " . $first->description() . "<br />";

?>