
angular.module('app.controllers', [])

.controller('AngelHack', function ($scope,$state,ClockSrv) {

   $scope.questionTitle = "Question";
   $scope.listAnswer = [];
   $scope.winText;
   $scope.isDisconnected = false;
   $scope.userID;
   $scope.timeOut = 15;
 
   $scope.clockTime ;
   $scope.isSubmitAsw = false;
    
   $scope.startClock = function(){
        $scope.clockTime = $scope.timeOut;

        ClockSrv.startClock(function(){
            $scope.clockTime -= 1 ;

            if($scope.clockTime == 0){
              //$scope.submitAnswer(0);
              ClockSrv.stopClock();
              $scope.isSubmitAsw = true;
            }
        });
   }
  
   $scope.setUserDisconnected = function (isDisconnected){
      $scope.isDisconnected = isDisconnected;
      console.log("LOG DISCOONECTED = " + $scope.isDisconnected);
      $scope.$apply();
   }

   $scope.submitAnswer = function (aid) {
      if($scope.isSubmitAsw == true)
        return;

    $('#myButton' + aid).css({backgroundColor: 'yellow'});

      $scope.isSubmitAsw = true;
      ClockSrv.stopClock();
      console.log("Radio value = " + aid);
      submitChoice(aid,$scope.timeOut - $scope.clockTime);
   };

   $scope.onStartGame = function(){

   };

   $scope.rejoinGame = function(){
      //$window.location.reload(true);
      Server.connect();
   }

   $scope.goToMainPage = function(userID){
      console.log("Welcome page goin to");
      $scope.userID = userID;
      $state.go("welcomePage");
   }

   $scope.showResult = function(isWin){
      
      if(isWin){
        $scope.winText = "CORRECT!";
      }else{
        $scope.winText = "WRONG!";  
        $scope.winText += " \n The correct answer is " + $scope.listAnswer[currentAid];
      }

      $state.go("resultPage");
   }

   $scope.showWinner = function(){
        $scope.winText = "YOU WIN!";
        $state.go("resultPage");
   }

   $scope.goToQuestionPage = function(title, choices){
    
      $('.button').css({backgroundColor: '#f8f8f8'});

      $scope.isSubmitAsw = false;
      $scope.questionTitle = title;
      $scope.listAnswer = choices;
      $scope.startClock();
      $state.go("questionPage");

   };

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
  