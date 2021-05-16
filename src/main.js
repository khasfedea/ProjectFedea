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
        document.getElementById(comment_id).innerHTML = [data];
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