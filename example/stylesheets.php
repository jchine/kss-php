<?php
/** 
* Example used Kss-php.
*
* Used preprocessor CSS (Sass, Less. Stylus is not in Php )
* 
* http://www.imaginanet.com/blog/preprocesar-hojas-de-estilo-con-less.html
* Composer include css preproccessors (Less, Sass, Scss, Stylus)
*      "require": {
*      ...
*      "leafo/lessphp": ">=0.3.8",
*      "leafo/scssphp": ">=0.0.4",
*      ...
*      }
*/

// Installed via Composer 
require_once __DIR__.'/../vendor/autoload.php';  

// Loading templates with Twig
$loader = new Twig_Loader_Filesystem(__DIR__.'/resources/views');
// Desactive autoescape for include content of another template render.
$twig = new Twig_Environment($loader, array(
    'cache' => __DIR__.'/cache',
    'autoescape' => false
));

// Load Css files for create documentation
$kss = new Kss\Parser(__DIR__."/resources/public/css");

$content = "";
$sections = $kss->sections(); // List of sections 
foreach ($sections as $key => $section) { // $key = '2.1.1'. Section number
    // Render template section example block.
    $block = $twig->render('styleguide.twig', array('styleguide_block' => $key));

    // Render template section and save.
    $content .= $twig->render('_styleguide_block.twig', array('section' => $section, 'example_html' => $block));
}

// Render template html
echo $twig->render('layout.twig', array( 'content' => $content));
?>