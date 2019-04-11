$barer = "";

function getCookie(name)
{
    let cookie = document.cookie.split(";");
    let result = undefined;
    $.each(cookie, function (index) {
        cookie[index] = cookie[index].trim();
        let c = cookie[index].split("=");
        if (c[0] == name)
        {
            result = c[1];
        }
    });
    return result;
}

function auth()
{
    var fd = new FormData();
    fd.append("login", "admin");
    fd.append("password", "adminWSR");
    $.ajax({
        url: "/auth",
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "json"
    }).done(function (data) {
        if (data.status == 200)
        {
            console.log(data.token);
            document.cookie = "hash=" + data.token;
        }
    });
}

function create_post()
{
    var fd = new FormData();
    fd.append('title', $("#title__").val());
    fd.append('anons', $("#anons__").val());
    fd.append('text', $("#text__").val());
    fd.append('tags', $("#tags__").val());
    let upload_img = $("#file");
    console.log(upload_img.prop('files')[0]);
    fd.append('img', upload_img.prop('files')[0]);
    $.ajax({
        url: "/posts",
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "json",
        headers: {"Authorization": getCookie("hash")}
    }).done(function (data) {
        if (data.status == 201)
        {
            $.ajax({
                url: "post_images/save_images.php",
                method: "POST",
                data: fd,
                contentType: false,
                processData: false
            }).done(function (data2) {
                if (data2 == "success uploaded")
                {
                    console.log(data.post_id);
                }
            });
        }
        else
        {
            console.log(data);
        }
    });
}

function update_post()
{
    var fd = new FormData();
    fd.append('title', 'title');
    fd.append('anons', 'anons');
    fd.append('text', 'text');
    fd.append('tags', 'tag1, tag2');
    let post_id = $('#post_id').val();
    let upload_img = $("#file");
    console.log(upload_img.prop('files')[0]);
    fd.append('img', upload_img.prop('files')[0]);
    $.ajax({
        url: "/posts/" + post_id,
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "json",
        headers: {"Authorization": getCookie("hash")}
    }).done(function (data)
    {
        if (data.status == 201)
        {
            $.ajax({
                url: "post_images/save_images.php",
                method: "POST",
                data: fd,
                contentType: false,
                processData: false
            }).done(function (data2) {
                if (data2 == "success uploaded")
                {
                    console.log(data.post);
                }
            });
        }
        else
        {
            console.log(data);
        }
    });
}

function delete_post()
{
    let post_id = $("#post_id").val();
    $.ajax({
        url: "/posts/" + post_id,
        method: "DELETE",
        headers: {"Authorization": getCookie("hash")}
    }).done(function (data) {
        if (data.status == 201)
        {
            console.log("success deleted");
        }
        else
        {
            console.log(data);
        }
    })
}

function get_all_posts()
{
    $.ajax({
        url: "/posts",
        method: "GET",
        headers: {"Authorization": getCookie("hash")}
    }).done(function (data) {
        if (data.status == 201)
        {
            console.log(JSON.parse(data.posts));
        }
        else
        {
            console.log(data);
        }
    });
}

function get_post()
{

    let post_id = $("#post_id").val();
    $.ajax({
        url: "/posts/" + post_id,
        method: "GET",
        headers: {"Authorization": getCookie("hash")}
    }).done(function (data) {
        if (data.status == 200)
        {
            console.log(JSON.parse(data.post));
        }
        else
        {
            console.log(data);
        }
    })
}

function create_comment()
{
    let author = $("#author_name").val();
    let post_id = $("#post_id").val();
    let comment = $("#comment").val();
    let fd = new FormData();
    fd.append("author", author);
    fd.append("comment", comment);
    $.ajax({
        url: "/posts/"+post_id+"/comments",
        method: "POST",
        data: fd,
        contentType: false,
        processData: false,
        headers: {"Authorization": getCookie("hash")}
    }).done(function (data) {
        console.log(data);
    });
}

function delete_comment()
{
    let post_id = $("#post_id").val();
    let comment_id = $("#comment_id").val();
    $.ajax({
        url: "/posts/" + post_id + "/comments/" + comment_id,
        method: "DELETE",
        headers: {"Authorization": getCookie("hash")}
    }).done(function (data) {
        if (data.status == 201)
        {
            console.log("success deleted");
        }
        else
        {
            console.log(data);
        }
    })
}

function search_post()
{
    let tag = $("#tag_name").val();
    console.log(tag);
    $.ajax({
        url: "posts/tag/" + tag,
        method: "GET",
        headers: {"Authorization": getCookie("hash")}
    }).done(function (data) {
        console.log(data);
    })
}