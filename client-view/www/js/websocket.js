  var Server;
  var qid = 0;
  var currentAid = 0;
  var timeOut = 15;
  
    function nextQuestion() {//{"id":"request_next_question","qid":"0"}
      var arr = '{"id":"request_next_question","qid":"' + qid + '"}';
      Server.send( arr );
    }


function submitChoice(aid,time) {
      var arr = '{"id":"request_submit_choice","qid":"' + qid + '","aid":"' + aid + '","time":"' + time + '"}';
      Server.send( arr );
}

    function send( text ) {
      // var arr = '{"id":"message","text":"' + text + '"}';
      // Server.send( arr );
      Server.send( text );
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
        console.log( "Connected NND." );
        var arr = '{"id":"notify_role","is_admin":"0"}';
        Server.send( arr );

      });

      //OH NOES! Disconnection occurred.
      Server.bind('close', function( data ) {
        console.log( "Disconnected." );
        angular.element(document.getElementById('AngelHackController')).scope().setUserDisconnected(true);
       
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
            var user_id = parseInt(jsonObj["user_id"]);
             angular.element(document.getElementById('AngelHackController')).scope().setUserDisconnected(false);
            angular.element(document.getElementById('AngelHackController')).scope().goToMainPage(user_id);
        }else if(jsonObj["id"] == "request_next_question"){
       
          qid = parseInt(jsonObj["qid"]);
          var questions = jsonObj["question"];
          currentAid =  questions["aid"]; // Dap An
          console.log("Dap an = " + currentAid);
          var title =  questions["title"]; // Cau Hoi
          var choices = questions["choices"]; // Cac Dap An
          angular.element(document.getElementById('AngelHackController')).scope().goToQuestionPage(title,choices);

        }else if(jsonObj["id"] == "request_show_result"){
          qid = parseInt(jsonObj["qid"]);
          var isCorrect = jsonObj["result"] == "true";
          angular.element(document.getElementById('AngelHackController')).scope().showResult(isCorrect);

        }else if(jsonObj["id"] == "request_show_ranking"){
          //you are the winner
          angular.element(document.getElementById('AngelHackController')).scope().showWinner();

        }
      });

      Server.connect();
    });