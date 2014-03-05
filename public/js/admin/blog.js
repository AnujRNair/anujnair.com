$(document).ready(function() {

    $("#blogTags").on("click", ".existingTag", function(event) {
        event.preventDefault();
        $('#tagError').html('<img src="/img/icons/loading.gif" />');
        var tagId = $(this).attr('id').substr(3);
        var blogId = parseInt($('#blogId').html());
        if ($(this).hasClass('existingTag') == false) {
            return false;
        }
        var adding = ($(this).hasClass('availableTag') ? 1 : 0);
        $.ajax({
            type: "POST",
            url: "/admin/blog/assigntag/",
            cache: false,
            dataType: "json",
            context: $(this),
            data: {
                'adding' : adding,
                'tagId' : tagId,
                'blogId' : blogId
            },
            success: function(data) {
                $('#tagError').html('');
                if (data.status == 'success') {
                    if (adding == 1) {
                        $(this).removeClass('availableTag');
                        $(this).addClass('assignedTag');
                        $(this).appendTo('#assignedTags');
                        $(this).find('img').attr("src", '/img/icons/bullet_delete.png');
                    } else {
                        $(this).removeClass('assignedTag');
                        $(this).addClass('availableTag');
                        $(this).appendTo('#availableTags');
                        $(this).find('img').attr("src", '/img/icons/bullet_add.png');
                    }
                } else {
                    $('#tagError').html(data.message).css({'color' : '#FF0000'});
                }
            },
            error: function() {
                $('#tagError').html('Error assigning tags').css({'color' : '#FF0000'});
            }
        });
    });

});