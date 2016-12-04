<?php
  class Crossword {
    private $cells;
    private $quests;
    private $ID = 0;
    static $IDcount = 0;
    function __construct($cells = [], $quests = []) {
      $this->cells = $cells;
      $this->quests = $quests;
      $this->ID = Crossword::$IDcount++;
    }
    static function from_url($url) {
      $file = file_get_contents($url);
      $file = str_replace(["\r\n", "\r", "\025"], "\n", $file);
      $tmp = explode("\n", $file);
      $cells = [];
      $quests = [];
      $delimiterOverflow = false;
      $delimiter = null;
      foreach ($tmp as $index => $value) {
        if ($delimiter == null) {
          if (!preg_match("/^[\"\']?crossword[\"\']?(.).*$/", $value)) {
            throw new Error("Error in Plugin: There is no valid crossword-csv-file at '" . $url . "'");
          }
          $delimiter = preg_replace('/^[\"\']?crossword[\"\']?(.).*$/i', '$1', $value);
        }
        else if (preg_match('/^[\"\']?questions[\"\']?' . $delimiter . '(' . $delimiter . '+)$/i', $value)) {
          $delimiterOverflow = strlen(preg_replace('/^[\"\']?questions[\"\']?' . $delimiter . '(' . $delimiter . '+)$/i', '$1', $value));
        }
        else if ($delimiterOverflow == false) {
          $cells[] = explode($delimiter, $value);
        }
        else {
          $tmp = explode($delimiter, $value, 2);
			    if (count($tmp) > 1) {
            $tmp[1] = preg_replace('/[' . $delimiter . ']{' . $delimiterOverflow . '}$/i', '', $tmp[1]);
            $tmp[1] = preg_replace('/^[\"\'](.*)[\"\']$/i', '$1', $tmp[1]);
            $tmp[1] = preg_replace(['/\"\"/i', '/\'\'/i'], ['"','\''], $tmp[1]);
			    }
          $quests[$tmp[0]] = $tmp[1];
        }
      }
      return new Crossword($cells, $quests);
    }
    public function get_cell($x = 0, $y = 0) {
      if (isset($this->cells[$y]) && isset($this->cells[$y][$x])) {
        return $this->cells[$y][$x];
      }
      return false;
    }
    public function get_rows() {
      return count($this->cells);
    }
    public function get_cells($y) {
      if(isset($this->cells[$y])) {
        return count($this->cells[$y]);
      }
      return false;
    }
    public function get_quest($index) {
      if (isset($this->quests[$index])) {
        return $this->quests[$index];
      }
      return false;
    }
    public function generate_html($options = []) {
      $checkButton = $options['check-button'] == 'true';
      $float = $options["float"];
      $x = -1;
      $y = -1;
      $arrows = [
        'up' => '&#11014;',
        'down' => '&#11015',
        'right' => '&#10145;',
        'left' => '&#11013;'
      ];
      $max_y = $this->get_rows();
      $max_x = 0;
      $regex = '/^([0-9]+)\\#(up|down|right|left)$/';
      $return = '<crossword-crossword float="' . $float . '">';
      $return .= '<table id="crossword-' . $this->ID . '">';
      while(++$y < $max_y) {
        $max_x = $this->get_cells($y);
        $x = -1;
        $return .= '<tr>';
        while(++$x < $max_x) {
          $content = $this->get_cell($x, $y);
          if (empty($content)) {
            $return .= '<td class="crossword-empty"><span id="crossword-' . $this->ID . '-field-' . $x . '-' . $y . '"></span></td>';
          }
          else if (preg_match($regex, $content)) {
            $index = preg_replace($regex, '$1', $content);
            $direction = preg_replace($regex, '$2', $content);
            $quest = $this->get_quest($index);
            $return .= '<td class="crossword-quest"><button id="crossword-' . $this->ID . '-quest-' . $index . '" onclick="crosswordShowQuest(' . $this->ID . ', ' . $index . ');"><span>' . $arrows[$direction] . '</span></button></td>';
          }
          else {
            $return .= '<td class="crossword-field"><input type="text" maxlength="1" id="crossword-' . $this->ID . '-field-' . $x . '-' . $y . '" data-correct-value="' . $content . '" /></td>';
          }
        }
        $return .= '</tr>';
      }
      $return .= '</table>';
      
      $return .= '<div class="crossword-button-group">';
      $return .= '<button onclick="crosswordReset(' . $this->ID . ')">' . crossword_translate('Reset crossword') . '</button>';
      if ($checkButton) {
        $return .= '<button onclick="crosswordCheck(' . $this->ID . ')">' . crossword_translate('Check crossword') . '</button>';
      }
      $return .= '</div>';
      $return .= '</crossword-crossword>';
      if($this->ID == 0) {
        $return .= '<crossword-overlay id="crossword-dialog" onclick="crosswordHideQuest()">';
        $return .= '<crossword-dialog onclick="event.stopPropagation();return false;">';
        $return .= '<crossword-header>' . crossword_translate('Question dialog') . '</crossword-header>';
        $return .= '<crossword-content id="crossword-dialog-content"></crossword-content>';
        $return .= '<crossword-footer><button onclick="crosswordHideQuest()">' . crossword_translate('Close dialog') . '</button></crossword-footer>';
        $return .= '</crossword-dialog>';
        $return .= '</crossword-overlay>';
        $return .= '<style type="text/css">';
        $return .= file_get_contents(__DIR__ . '/style.css');
        $return .= '</style>';
      };
      $return .= '<script type="text/javascript">';
      if ($this->ID == 0) {
        $return .= file_get_contents(__DIR__ . '/script.js');
      }
      $return .= 'crosswordQuests[' . $this->ID . '] = ' . json_encode($this->quests);
      $return .= '</script>';
      return $return;
    }
  }
?>