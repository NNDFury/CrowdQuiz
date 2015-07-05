
angular.module('app.controllers', [])

.controller('AngelHack', function ($scope,$state,ClockSrv) {
        
    $scope.quantityPlayer = 0;
    $scope.questionContent;
    $scope.answerList;
    $scope.rightAnswer;
    $scope.timeOut = 15;
    $scope.clockTime ;
    $scope.listModelRank = [];

     $scope.startClock = function(){
        $scope.clockTime = $scope.timeOut;

        ClockSrv.startClock(function(){
            $scope.clockTime -= 1 ;

            if($scope.clockTime == 0){
              //$scope.submitAnswer(0);
              ClockSrv.stopClock();
            }
        });
   }

    $scope.getRanking = function() {
      getRanking(qid);
    }

    $scope.gotoFinalPage = function(passObjectArray) {
      $scope.listModelRank = passObjectArray;
      $state.go("finalPage");
    }

    
    $scope.getResult = function(){
          getResult();
         //$state.go("resultPage");
    }

    $scope.updatePlayerQuantity = function (number) {
       $scope.quantityPlayer = number;
       $scope.$apply();
    };

   
    $scope.updateQuestion = function(title, choices){
       $scope.questionContent =  title;
       $scope.answerList = choices;
       //$scope.$apply();
    };


   $scope.goToNextQuestion = function () {
     nextQuestion();
   }

    $scope.goToQuestionPage = function(title, choices){
       $scope.startClock();
       $scope.updateQuestion(title, choices);
       $state.go("questionPage");
     
    }    

    $scope.currentPlayers = 0;
    $scope.correctNumber = 0;
    $scope.wrongNumber = 0;
    $scope.resultType = 0;
    $scope.resultButtonName;

    $scope.gotoResultPage = function(correct,wrong,type){
      $scope.correctNumber = correct;
      $scope.wrongNumber = wrong;
      $scope.currentPlayers = correct + wrong;
      $scope.resultType  = type;

      switch($scope.resultType) {
            case 1:
            //RESULT_GO_TO_NEXT_QUESTION
            $scope.resultButtonName = "Go to Next Question";
            break;

            case 2:
            //RESULT_ONLY_1_WINNER
            $scope.resultButtonName = "Show Final Result";
            break;

            case 3:
            //RESULT_SHOW_RANKING
            $scope.resultButtonName = "Show Final Result";
            break;

            case 4:
            //RESULT_NOBODY_WIN
            $scope.resultButtonName = "Restart";
            break;
        }


          console.log("Button type = " + type);
          console.log("Button name = " + $scope.resultButtonName);

          $state.go("resultPage");
     
    }

    $scope.handleNextButton = function(){
        switch($scope.resultType) {
            case 1:
            //RESULT_GO_TO_NEXT_QUESTION
            nextQuestion();
            break;

            case 2:
            //RESULT_ONLY_1_WINNER
            getRanking();
            break;

            case 3:
            //RESULT_SHOW_RANKING
            getRanking();
            break;

            case 4:
            //RESULT_NOBODY_WIN
            
            $scope.restartGame();
            break;
          }
    }

    $scope.restartGame = function(){
        restartGame();
        $scope.quantityPlayer = 0;
        qid = 0;
        $state.go("page1");
    }
})

.factory('ClockSrv', function($interval){
  var clock = null;
  var service = {
    startClock: function(fn){
      if(clock === null){
        clock = $interval(fn, 1000);
      }
    },
    stopClock: function(){
      if(clock !== null){
        $interval.cancel(clock);
        clock = null;
      }
    }
  };

  return service;
})