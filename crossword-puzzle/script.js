if ("crosswordQuests" in self == false) {
  self.crosswordQuests = [];
};

function crosswordShowQuest($crosswordId, $index) {
  document.getElementById('crossword-dialog-content').textContent = crosswordQuests[$crosswordId][$index];
  document.getElementById('crossword-dialog').setAttribute('show', '');
};

function crosswordHideQuest() {
  document.getElementById('crossword-dialog-content').textContent = '';
  document.getElementById('crossword-dialog').removeAttribute('show');
};

function crosswordReset($crosswordId) {
  var $inputs = document.getElementById('crossword-' + $crosswordId).getElementsByTagName('input');
  [].forEach.call($inputs, function ($input) {
    $input.style.backgroundColor = '';
    $input.value = '';
  });
};

function crosswordCheck($crosswordId) {
  var $inputs = document.getElementById('crossword-' + $crosswordId).getElementsByTagName('input');
  [].forEach.call($inputs, function ($input) {
    if ($input.value.toLowerCase() == $input.getAttribute('data-correct-value').toLowerCase()) {
      $input.style.backgroundColor = '#080';
    } else {
      $input.style.backgroundColor = '#f00';
    };
  });
}