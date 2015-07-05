<?php
require 'angel_room.php';
require 'angel_user.php';
require 'constant_variables.php';
require 'angel_round_result.php';
require 'angel_question_generator.php';

/**
* 
*/
class GameController
{
	public $room;
	public $Server;
	private $questionGenerator;

	public function __construct(PHPWebSocket $s){
		$this->room = new AngelRoom;
		//var_dump($room);
		$this->Server = $s;
		$this->questionGenerator = new AngelQuestionGenerator();
		$this->questionGenerator->generateRandomly();
		// echo "GameController__construct";
	}
	//lisener
	public function onAdminJoin($idAdmin){
		if ($this->room->admin == -1) {
			$this->room->admin = $idAdmin;
		}else{
			$this->Server->wsRemoveClient($idAdmin);
		}

	}

	public function onUserJoin($idUser){		
		if($this->room->progress == AngelRoom::P_NEW && $this->room->adminNotJoinedYet() == false) {
			$this->room->addUser($idUser);
			$arr = '{"id":"' .ConstantVariables::KEY_REQUEST_USER_LOGIN. '","count":"'.count($this->room->userModelArray).'"}';
			$this->sendToAdmin($arr);

			$arr2 = '{"id":"' .ConstantVariables::KEY_REQUEST_USER_LOGIN. '","user_id":"'.$idUser.'"}';	
			$this->sendToUserWithID($idUser, $arr2);
		} else {
			$this->Server->wsRemoveClient($idUser);
		}
	}

	public function onUserLeave($idUser) {
		if($this->room->admin == $idUser) {
			//disconnect admin	
			$this->room->admin = -1;
			$this->restartGame();
			echo 'admin leave';	
		} else {
			$this->room->removeUserWithID($idUser);

			if($this->room->progress == AngelRoom::P_NEW && $this->room->adminNotJoinedYet() == false) {
			
			$arr = '{"id":"' .ConstantVariables::KEY_REQUEST_USER_LOGIN. '","count":"'.count($this->room->userModelArray).'"}';
			$this->sendToAdmin($arr);
		} 
			echo 'user leave with id ' . $idUser;
		}
	}

	private function getCurrentQuestionModel() {
		return $this->questionGenerator->getQuestionAtIndex($this->room->progress - 1);
	}


	public function restartGame() {
		$this->room->reset();
		$this->questionGenerator->generateRandomly();
		// for($i = 0, $count = count($this->Server->wsClients); $i < $count; $i++) {
		// 	$id = $this->Server->wsClients[$i];
		// 	if($id != $this->room->admin) {
		// 		$this->Server->wsRemoveClient($id);
		// 	}
		// }

		foreach ( $this->Server->wsClients as $id => $client ) {
			if($id != $this->room->admin) {
				$this->Server->wsRemoveClient($id);
			}
		}
	}

	public function nextQuestion($qid){
		if(count($this->room->userModelArray) == 0) return;

		if($qid == $this->room->progress) {
			if($qid >= AngelRoom::P_Q1) {
				$this->disconnectAllUsersThatHaveWrongAnswers();
			}
			
			$stillHaveQuestionToGo = $this->room->toNextProgress();
			if($stillHaveQuestionToGo == true) {
				$question = $this->getCurrentQuestionModel();
				$arr = '{"id":"' .ConstantVariables::KEY_REQUEST_NEXT_QUESTION. '","qid":"'.$this->room->progress.'",' . $question->toJSONKeyValue().'}';	
				$this->sendToAll($arr);
			} else {

			}
		}
	}

	private function disconnectAllUsersThatHaveWrongAnswers() {
		foreach ($this->room->userModelArray as $userModel) {
			# code...
			$currentAnswer = $userModel->getAnswerAtIndex($this->room->progress - 1);
			if($currentAnswer == null || $currentAnswer->isCorrect == false) {
				echo 'disconnect user with id ' . $userModel->id;
				$this->Server->wsRemoveClient($userModel->id);
			} 
		}
	}

	public function submitChoice($idUser, $answer) {
		if($this->room->isReadyForSubmitChoices() == false) return;

		if($answer->qid == $this->room->progress) {
			$userModel = $this->room->userWithID($idUser);
			if($userModel != null) {
				$userModel->recordAnswer($answer, $this->room->progress - 1);
			} else {

			}
		}
		
	}

	public function showResult($qid){
		if($qid == $this->room->progress) {
			$question = $this->getCurrentQuestionModel();
			$numberOfCorrect = 0;
			$numberOfWrong = 0;
			$arrCorrect = '{"id":"' .ConstantVariables::KEY_REQUEST_SHOW_RESULT. '","qid":"'.$qid.'","result":"true"}';
			$arrWrong = '{"id":"' .ConstantVariables::KEY_REQUEST_SHOW_RESULT. '","qid":"'.$qid.'","result":"false"}';
			$lastWinnerID = 0;
			foreach ($this->room->userModelArray as $userModel) {
				# code...
				$currentAnswer = $userModel->getAnswerAtIndex($this->room->progress - 1);
				if($currentAnswer != null && $question->isCorrect($currentAnswer)) {
					 $numberOfCorrect++;
					 $this->sendToUserWithID($userModel->id, $arrCorrect);
					 $currentAnswer->isCorrect = true;
					 $lastWinnerID = $userModel->id;
				} else {
					$numberOfWrong++;
					$this->sendToUserWithID($userModel->id, $arrWrong);
					$currentAnswer->isCorrect = false;
				}
			}
			$roundResult = new AngelRoundResult($numberOfCorrect, $this->room->isLastQuestion(), $lastWinnerID);
			$arrAdmin = '{"id":"' .ConstantVariables::KEY_REQUEST_SHOW_RESULT. '","qid":"'.$qid.'","correct":"'.$numberOfCorrect.'","wrong":"'.$numberOfWrong.'", ' . $roundResult->toJSONKeyValue() .'}';
			$this->sendToAdmin($arrAdmin);
		}
	}

	public function showRanking($qid) {
		if($qid == $this->room->progress) {
			
			$this->disconnectAllUsersThatHaveWrongAnswers();
			$this->room->sortUserArrayByTotalTime();
			$arrAdmin = '{"id":"' .ConstantVariables::KEY_REQUEST_SHOW_RANKING. '", "rankingArray":[';
			for($count = count($this->room->userModelArray), $i = 0; $i < $count; $i++) {
				$userModel = $this->room->userModelArray[$i];
				$userJSON = $userModel->toJSONKeyValue();
				$arrAdmin = $arrAdmin . $userJSON;
				if($i < $count - 1) {
					$arrAdmin = $arrAdmin . ',';
				} else {
					$arrAdmin = $arrAdmin . ']}';
				}
			}
			$this->sendToAdmin($arrAdmin);

			$winner = $this->room->userModelArray[0];
			$arrWinner = '{"id":"' .ConstantVariables::KEY_REQUEST_SHOW_RANKING. '"}';
			$this->sendToUserWithID($winner->id, $arrWinner);
			$this->room->progress = AngelRoom::P_END;
		}
	}

	private function sendToAdmin($dict) {
		if($this->room->adminNotJoinedYet()) return;
		$this->Server->wsSend($this->room->admin, $dict);
	}

	private function sendToAll($dict) {
		if($this->room->adminNotJoinedYet()) return;

		foreach ( $this->Server->wsClients as $id => $client ) {
			$this->sendToUserWithID($id, $dict);
		}
	}

	private function sendToAllNormalUsers($dict) {
		if($this->room->adminNotJoinedYet()) return;

		foreach ( $this->Server->wsClients as $id => $client ) {
			if($id != $this->room->admin) {
				$this->sendToUserWithID($id, $dict);
			}
		}
	}

	private function sendToUserWithID($id, $dict) {
		$this->Server->wsSend($id, $dict);
	}

}
?>