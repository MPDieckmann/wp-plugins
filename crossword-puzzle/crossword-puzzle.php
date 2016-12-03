<?php
  /*
  Plugin Name: Crossword Puzzle Plugin
  Plugin URI: https://mpdieckmann.github.io/wp_plugins/crossword-puzzle
  Description: Displays Crossword Puzzles which were previously created and uploaded as an CSV-Table
  Version: 2016.12.02
  Author: Marc Phillip Dieckmann
  Author URI: https://mpdieckmann.github.io/
  License: GPL3
  License URI: https://www.gnu.org/licenses/gpl-3.0.html
  */
  require_once 'crossword.class.php';

  add_shortcode("crossword", function($atts) {
    $parsed_atts = shortcode_atts([
      'url' => null,
      'ckeck-button' => 'true',
      'float' => 'none'
    ], $atts);
    if ($parsed_atts['url'] === null) {
      return '<p class="error">If you want to create a crossword-puzzle please use this shortcode: <code>[crossword url="valid-url"]</code></p>';
    }
    $crossword = Crossword::from_url($parsed_atts['url']);
    return $crossword->generate_html([
      'check-button' => $parsed_atts['ckeck-button'],
      'float' => $parsed_atts['float']
    ]);
  });

  global $crossword_translations;  
  if (file_exists(__DIR__.'/l10n/' . get_locale() . '.json')) {
    $crossword_translations = json_decode(file_get_contents(__DIR__.'/l10n/' . get_locale() . '.json'));
  }
  else {
    $crossword_translations = json_decode('{}');
  }

  function crossword_translate($text) {
    global $crossword_translations;
    if (property_exists($crossword_translations, $text)) {
      return $crossword_translations->$text;
    }
    return $text;
  }
?>
