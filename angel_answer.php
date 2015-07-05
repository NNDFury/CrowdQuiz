<?php
/**
* 
*/
class AngelAnswer
{
	public $qid = 0;
	public $aid = 0;
	public $time;
	public $isCorrect = false;

	public function __construct($pQID, $pAID, $pTime) {
		$this->qid = $pQID;
		$this->aid = $pAID;
		$this->time = $pTime;
	}
}
?>