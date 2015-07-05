<?php
/**
* 
*/
class AngelRoom
{
	const P_NEW = 0;
    const P_Q1 = 1;
    const P_Q2 = 2;
    const P_Q3 = 3;
    const P_END = 4;
	public $admin = -1;
	public $userModelArray = array();
	public $progress = AngelRoom::P_NEW;


	public function reset() {
		$this->progress = AngelRoom::P_NEW;
		$this->userModelArray = array();
	}

	public function addUser($idUser) {
		$user = new AngelUser();
		$user->id = $idUser;
		array_push($this->userModelArray, $user);
	}

	public function adminNotJoinedYet() {
		return $this->admin == -1;
	}

	public function isLastQuestion() {
		return $this->progress == AngelRoom::P_Q3;
	}

	public function toNextProgress() {
		if($this->progress + 1 >= AngelRoom::P_END) {
			return false;
		} else {
			$this->progress++;
			return true;
		}
	}

	public function removeUserWithID($idToSearch) {
		$count = count($this->userModelArray);
		for($i = 0; $i < $count; $i++) {
			$userModel = $this->userModelArray[$i];
			if($userModel->id == $idToSearch) {
				unset($this->userModelArray[$i]);
				$this->userModelArray = array_values($this->userModelArray);
				break;
			}
		}
	}

	function userCompare($userA, $userB) {
		$totalTimeA = $userA->getTotalTime();
		$totalTimeB = $userB->getTotalTime();
		if($totalTimeA == $totalTimeB) return 0;
		return ($totalTimeA < $totalTimeB) ? -1 : 1;
	}

	public function sortUserArrayByTotalTime() {

		usort($this->userModelArray, array($this, "userCompare"));
	}

	public function isReadyForSubmitChoices() {
		return $this->progress > AngelRoom::P_NEW && $this->progress < AngelRoom::P_END;
	}

	public function userWithID($idToSearch) {

		foreach ($this->userModelArray as $userModel) {
			# code...
			if($userModel->id == $idToSearch) {
				return $userModel;
			}
		}
		return null;
	}
}
?>