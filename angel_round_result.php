<?php
/**
* 
*/
class AngelRoundResult
{
	const RESULT_GO_TO_NEXT_QUESTION = 1;
	const RESULT_ONLY_1_WINNER = 2;
	const RESULT_SHOW_RANKING = 3;
	const RESULT_NOBODY_WIN = 4;

	public $resultType = AngelRoundResult::RESULT_GO_TO_NEXT_QUESTION;
	public $winnerID = -1;

	public function __construct($correctCount, $isLastRound, $pWinnerID) {
		if($correctCount == 0) {
			$this->resultType = AngelRoundResult::RESULT_NOBODY_WIN;
		} elseif($correctCount == 1) {
			$this->resultType = AngelRoundResult::RESULT_ONLY_1_WINNER;
			$this->winnerID = $pWinnerID;
		} else {
			if($isLastRound == true) {
				$this->resultType = AngelRoundResult::RESULT_SHOW_RANKING;
			} else {
				$this->resultType = AngelRoundResult::RESULT_GO_TO_NEXT_QUESTION;
			}
		}
	}

	public function toJSONKeyValue() {
		$resultString = '"roundResult":{"type":"' . $this->resultType . '" , "winnerID":"' . $this->winnerID . '"}';
		return $resultString;
	}
}
?>