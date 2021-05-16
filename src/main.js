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
    $.post("handler.php",
    {
        post_sent: true,
        content_field: postText,
        image_field: null
    },
    function(data, status){
        $(data).insertAfter('.post-window');
        document.getElementById("postText").value = "";
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