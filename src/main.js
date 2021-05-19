function makeButtonActive(name){
    document.getElementById(name).className += 'active';
}
function likePost(id){
    $.post("handler.php",
    {
        like: true,
        post_id: id
    },
    function(data, status){
        document_id = "like-count-"+id;
        if(document.getElementById(document_id).innerHTML < [data]){
            $("#"+id+".post .content .under-post .likes .like-button").text('Unlike');
        } else {
            $("#"+id+".post .content .under-post .likes .like-button").text('Like');
        }
        document.getElementById(document_id).innerHTML = [data];
    });
}
function likeComment(postid, commentid){
    $.post("handler.php",
    {
        like_comment: true,
        post_id: postid,
        comment_id: commentid
    },
    function(data, status){
        comment_id = "like-comment-"+postid+"-"+commentid;
        console.log(comment_id);
        if(document.getElementById(comment_id).innerHTML < [data]){
            $("#"+commentid+".message .content .under-comment .likes .like-button").text('Unlike');
        } else {
            $("#"+commentid+".message .content .under-comment .likes .like-button").text('Like');
        }
        document.getElementById(comment_id).innerHTML = [data];
    });
}
function postStatus(){
    postText = document.getElementById("postText").value;
    if(postText.length === 0) return;
    var form_data = new FormData();
    var postPicture = $('#postPicture').prop('files')[0];
    form_data.append('file', postPicture);
    form_data.append('content_field', postText);
    $.ajax({
        url: 'handler.php',
        dataType: 'text',
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: 'post',
        success: function(response){
            $(response).insertAfter('.post-window');
            document.getElementById("postText").value = "";
            document.getElementById("postPicture").value = "";
        }
    });
}
function fetchRequests(){
    $.post("handler.php",
    {
        fetch_friend_request: true
    },
    function(data){
        console.log(data);
        if(data == "new_friend"){
            $("#profile").css("background-color", "rgb(255,200,220)");
            $("#requests").css("background-color", "rgb(200,255,220)");
        } else {
            $("#profile").css("background-color", "");
            $("#requests").css("background-color", "");
        }
    });
}
$(document).ready(function(){
    $(".search-form").on('search', function () {
        if($(".search-form").val().length === 0){
            $(".search-field").attr("class", "search-field");
            return;
        }
        $.post("handler.php",
        {
            search_keyword: $(".search-form").val()
        },
        function(data){
            if(data.length === 0){
                data =
                '<div class="result-element">'+
                '<span>No result found.</span>'+
                '</div>';
            }
            $(".search-field").html(data);
            $(".search-field").attr("class", "search-field search-visible");
        });
    });
    $(function() {fetchRequests()});
    $(function() {
        setInterval(fetchRequests, 15000);
    });
})
function postComment(postid){
    comment_id = "commentText-"+postid;
    commentText = document.getElementById(comment_id).value;
    $.post("handler.php",
    {
        comment_sent: true,
        comment_field: commentText,
        post_id: postid
    },
    function(data, status){
        post_comment_id = "#post-comment-"+postid;
        comment_field_id = "commentText-"+postid;
        $(data).insertBefore(post_comment_id);
        $("#comment-count-"+postid).text(parseInt($("#comment-count-"+postid).text()) + 1);
        document.getElementById(comment_field_id).value = "";
    });
}
function showComments(id){
    comments_id = "comment-section-"+id;
    if (document.getElementById(comments_id).className == "messages"){
        document.getElementById(comments_id).className += "messagesVisible";
    } else {
        document.getElementById(comments_id).className = "messages";
    }
}
function deletePost(id){
    if (!confirm("Are you sure you want to remove this post?")){
        return;
    }
    $.post("handler.php",
    {
        delete_post_id: id
    },
    function(data, status){
        if([data] == 'unauthorized'){
            alert("You are not allowed to do that.");
        } else {
            $("#"+id+".post").remove();
        }
    });
}
function deleteComment(id, post_id){
    if (!confirm("Are you sure you want to remove this comment?")){
        return;
    }
    $.post("handler.php",
    {
        delete_comment_id: id
    },
    function(data, status){
        if([data] == 'unauthorized'){
            alert("You are not allowed to do that.");
        } else {
            $("#"+id+".message").remove();
            $("#comment-count-"+post_id).text(parseInt($("#comment-count-"+post_id).text()) - 1);
        }
    });
}
function sendFriendRequest(id){
    $.post("handler.php",
    {
        friend_request_id: id
    },
    function(data){
        if(data.length > 0){
            alert(data);
            return;
        }
        $(".add-friend#"+id).attr("class", "remove-friend");
        $(".remove-friend#"+id).attr("onclick", "cancelFriendRequest("+id+")");
        $(".remove-friend#"+id).text("Cancel Friend Request");
    });
}
function cancelFriendRequest(id){
    $.post("handler.php",
    {
        cancel_request_id: id
    },
    function(data){
        if(data.length > 0){
            alert(data);
            return;
        }
        $(".remove-friend#"+id).attr("class", "add-friend");
        $(".add-friend#"+id).attr("onclick", "sendFriendRequest("+id+")");
        $(".add-friend#"+id).text("Add Friend");
    });
}
function removeFriendRequest(id){
    if (!confirm("Are you sure you want to deny friend request?")){
        return;
    }
    $.post("handler.php",
    {
        remove_request_id: id
    },
    function(data){
        if(data.length > 0){
            alert(data);
            return;
        }
        $(".friendship-row#"+id).html('<a class="add-friend" id="'+id+'" onclick="sendFriendRequest('+id+')">Add Friend</a>');
    });
}
function acceptFriendRequest(id){
    $.post("handler.php",
    {
        accept_request_id: id
    },
    function(data){
        if(data.length > 0){
            alert(data);
            return;
        }
        $(".friendship-row#"+id).html('<a class="remove-friend" id="'+id+'" onclick="removeFriend('+id+')">Remove Friend</a>');
    });
}
function removeFriend(id){
    if (!confirm("Are you sure you want to remove friend?")){
        return;
    }
    $.post("handler.php",
    {
        remove_friend_id: id
    },
    function(data){
        if(data.length > 0){
            alert(data);
            return;
        }
        $(".remove-friend#"+id).attr("class", "add-friend");
        $(".add-friend#"+id).attr("onclick", "sendFriendRequest("+id+")");
        $(".add-friend#"+id).text("Add Friend");
    });
}
function GoToUser(id){
    var form = '';
    form += '<input type="hidden" name="profile" value="'+id+'">';
    $('<form action="profile.php" method="POST">'+form+'</form>').appendTo('body').submit();
}