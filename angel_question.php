<?php
require 'angel_answer.php';
/**
* 
*/
class AngelQuestion
{
	public $aid = 0;
	public $questionTitle;

	public $questionChoices = array();

	public function __construct($pAID, $pQuestionTitle, $pQuestionChoices) {
		$this->aid = $pAID;
		$this->questionTitle = $pQuestionTitle;
		$this->questionChoices = $pQuestionChoices;
	}

	public function isCorrect($answer) {
		return $answer->aid == $this->aid;
	}

	public function toJSONKeyValue() {
		$resultString = '"question":{"aid":"' .$this->aid. '","title":"' .$this->questionTitle.'","choices":[';
		$count = count($this->questionChoices);
		for($i = 0; $i < $count; $i++) {
			$choice = $this->questionChoices[$i];
			$resultString = $resultString . '"' . $choice . '"';
			if($i < $count - 1 ) {
				$resultString = $resultString . ',';
			} else {
				$resultString = $resultString . ']}';
			}
		}
		return $resultString;
	}
}
?>