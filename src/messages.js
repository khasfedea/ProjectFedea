function fetchMessages(id = $(".identification").first().attr("id")){
    $.post("handler.php",
    {
        query_messages: true,
        second_user: id
    },
    function(data){
        $(".message-field").html(data);
        $(".message-field").attr("id",id);
    });
}

function refreshMessages(){
    fetchMessages($(".message-field").attr("id"));
}

function sendMessage(message){
    $.post("handler.php",
    {
        send_message: message,
        recipient: $(".message-field").attr("id")
    },
    function(){
        fetchMessages($(".message-field").attr("id"));
        $('.message-enter').val("");
    });
}

$(document).ready(function(){
    $(function() {refreshMessages()});
    $(function() {
        setInterval(refreshMessages, 5000);
    });
    $('.message-enter').keypress(function (e) {
        if (e.which == 13) {
          $(function(){
              sendMessage($('.message-enter').val());
          });
          return false;
        }
      });
})