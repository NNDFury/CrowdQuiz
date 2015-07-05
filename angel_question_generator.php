<?php
require 'angel_question.php';
/**
* 
*/
class AngelQuestionGenerator
{
	public $questionArray = array();

	public function generateRandomly() {
		unset($questionArray);
		for($i = 0; $i < 3; $i++) {
			$questionTitle = '';
			$questionChoices = array();
			$correctAnswerID = 0;

			switch ($i) {
				case 0:
					# code...
					$questionTitle = 'What is the name of this competition?';
					array_push($questionChoices, "A/ Robocon 2015");
					array_push($questionChoices, "B/ Dota 2 Championship");
					array_push($questionChoices, "C/ AngelHack 2015");
					array_push($questionChoices, "D/ Olympic");
					$correctAnswerID = 2;
					break;
				
				case 1:
					# code...
					$questionTitle = 'Who is Goku \'s wife?';
					array_push($questionChoices, "A/ Vegeta");
					array_push($questionChoices, "B/ Bulma");
					array_push($questionChoices, "C/ Android 18");
					array_push($questionChoices, "D/ Chichi");
					$correctAnswerID = 3;
					break;

				case 2:
					# code...
					$questionTitle = 'Who is the first man to walk on the Mars?';
					array_push($questionChoices, "A/ Uncle Cuội");
					array_push($questionChoices, "B/ Sister Hằng");
					array_push($questionChoices, "C/ Neil Amstrong");
					array_push($questionChoices, "D/ None of the above");
					$correctAnswerID = 3;
					break;

					// case 3:
					// # code...
					// $questionTitle = 'q4';
					// array_push($questionChoices, "A/ Uncle Cuội");
					// array_push($questionChoices, "B/ Sister Hằng");
					// array_push($questionChoices, "C/ Yuri Gagarin");
					// array_push($questionChoices, "D/ Neil Amstrong");
					// $correctAnswerID = 3;
					// break;

					// case 4:
					// # code...
					// $questionTitle = 'q5';
					// array_push($questionChoices, "A/ Uncle Cuội");
					// array_push($questionChoices, "B/ Sister Hằng");
					// array_push($questionChoices, "C/ Yuri Gagarin");
					// array_push($questionChoices, "D/ Neil Amstrong");
					// $correctAnswerID = 3;
					// break;

					// case 5:
					// # code...
					// $questionTitle = 'q6';
					// array_push($questionChoices, "A/ Uncle Cuội");
					// array_push($questionChoices, "B/ Sister Hằng");
					// array_push($questionChoices, "C/ Yuri Gagarin");
					// array_push($questionChoices, "D/ Neil Amstrong");
					// $correctAnswerID = 3;
					// break;
			}


			$question = new AngelQuestion($correctAnswerID, $questionTitle, $questionChoices);
			array_push($this->questionArray, $question);
		}
		//xao' array
		// shuffle($questionArray);
		// $questionArray = array_values($questionArray);
	}

	public function getQuestionAtIndex($index) {
		if($index < 0 || $index >= count($this->questionArray)) return null;
		return $this->questionArray[$index];
	}



}
?>