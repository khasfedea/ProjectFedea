function fetchMessages(id = $(".friends .identification").first().attr("id"), scrollBottom=true){
    $.post("handler.php",
    {
        query_messages: true,
        second_user: id
    },
    function(data){
        $(".message-field").html(data);
        $(".message-field").attr("id",id);
        if(scrollBottom){
            $('.message-field').scrollTop($('.message-field').height());
        }
        $('.message-pane .identification img').attr("src", $(".friends .identification#"+id+" .avatar").attr("src"));
        $('.message-pane .identification span').text($(".friends .identification#"+id+" a").text());
    });
}

function refreshMessages(){
    fetchMessages($(".message-field").attr("id"), false);
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
    $(function() {fetchMessages($(".message-field").attr("id"), true)});
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