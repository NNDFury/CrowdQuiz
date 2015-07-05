var Server;
    var qid = 0;


    function nextQuestion() {//{"id":"request_next_question","qid":"0"}
      var arr = '{"id":"request_next_question","qid":"' + qid + '"}';
      Server.send( arr );
    }

    function restartGame() {
      var arr = '{"id":"request_restart_game"}';
      Server.send( arr );
    }

    function getResult() {
        var arr = '{"id":"request_show_result","qid":"' + qid + '"}';
        Server.send( arr );
    }

    function send( text ) {
      // var arr = '{"id":"message","text":"' + text + '"}';
      // Server.send( arr );
      Server.send( text );
    }

    function getRanking() {
      var arr = '{"id":"request_show_ranking","qid":"' + qid + '"}';
      Server.send( arr );
    }

    $(document).ready(function() {
      console.log('Connecting...');
      Server = new FancyWebSocket('ws://192.168.0.56:9301');

      $('#message').keypress(function(e) {
        if ( e.keyCode == 13 && this.value ) {
          log( 'You: ' + this.value );
          send( this.value );

          $(this).val('');
        }
      });

      //Let the user know we're connected
      Server.bind('open', function() {
        console.log( "Connected." );
        var arr = '{"id":"notify_role","is_admin":"1"}';
        Server.send( arr );
      });

      //OH NOES! Disconnection occurred.
      Server.bind('close', function( data ) {
        console.log( "Disconnected." );
      });

      //Log any messages sent from server
      Server.bind('message', function( payload ) {
        console.log( payload );
        var jsonObj; 
        try {
             jsonObj = JSON.parse(payload);
          } catch (e) {
          }
        console.log("payload=" + payload);
        if (!jsonObj) {
          return;
        };

        if(jsonObj["id"] == "response_user_login"){
             var count = parseInt(jsonObj["count"]);
    
             angular.element(document.getElementById('AngelHackController')).scope().updatePlayerQuantity(count);
    
        }else if(jsonObj["id"] == "request_next_question"){
            qid = parseInt(jsonObj["qid"]);
            var questions = jsonObj["question"];
            var aid =  questions["aid"]; // Dap An
            var title =  questions["title"]; // Cau Hoi
            var choices = questions["choices"]; // Cac Dap An
            angular.element(document.getElementById('AngelHackController')).scope().goToQuestionPage(title,choices);
        }else if(jsonObj["id"] == "request_show_result"){
          console.log("request_show_result");

          qid = parseInt(jsonObj["qid"]);
          var numOfCorrect = parseInt(jsonObj["correct"]);
          var numOfWrong = parseInt(jsonObj["wrong"]);
          var roundResultObj = jsonObj["roundResult"];
          var resultType = parseInt(roundResultObj["type"]);
          var winnerID = parseInt(roundResultObj["winnerID"]);

          switch(resultType) {
            case 1:
            //RESULT_GO_TO_NEXT_QUESTION
            break;

            case 2:
            //RESULT_ONLY_1_WINNER
            break;

            case 3:
            //RESULT_SHOW_RANKING
            break;

            case 4:
            //RESULT_NOBODY_WIN
            break;
          }

          angular.element(document.getElementById('AngelHackController')).scope().gotoResultPage(numOfCorrect,numOfWrong,resultType);
          
        } else if(jsonObj["id"] == "request_show_ranking"){
          //payload={"id":"request_show_ranking", "rankingArray":[{"id":"2","totalTime":"4" }]}
          var passObjectArray = [];

          var rankingArray = jsonObj["rankingArray"];
          for (var i = 0; i < rankingArray.length; i++){
            var i_id = rankingArray[i]["id"];
            var i_totalTime = rankingArray[i]["totalTime"];
            var i_rank = i + 1;
            var passObject = {
              rank : i_rank,
              id : i_id,
              score : i_totalTime
            }

            passObjectArray.push(passObject);
          }

          angular.element(document.getElementById('AngelHackController')).scope().gotoFinalPage(passObjectArray);
        
        }
});
        Server.connect();
    });