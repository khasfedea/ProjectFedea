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