<?php
/**
* 
*/
class AngelUser
{
	public $array_answer = array();
	public $id;
	public $totalTime = -1;

	public function recordAnswer($answer, $index) {
		$this->array_answer[$index] = $answer;
	}

	public function getAnswerAtIndex($index) {
		if($index < 0 || $index >= count($this->array_answer)) return null;
		return $this->array_answer[$index];
	}

	public function getTotalTime() {
		if($this->totalTime == -1) {
			$this->totalTime = 0;
			foreach ($this->array_answer as $answer) {
				# code...
				$this->totalTime += $answer->time;
			}
		}
		return $this->totalTime;

	}

	public function toJSONKeyValue() {
		$resultString = '{"id":"' . $this->id . '","totalTime":"'.$this->getTotalTime().'" }';
		return $resultString;
	}
}
?>