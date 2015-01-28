function writeAddRemove(id) {
    try {
        var json_str = getCookie('favorites');
        var arr = JSON.parse(json_str);
    } catch (e) {
        arr = [];
    }

    console.log(arr);
    console.log(id);
    console.log(arr.indexOf(id));
    if (arr.indexOf(id) >= 0) {
        document.write('<a href="#" title="Remove from favorites" onclick="javascript:remStockID('+id+'); window.location.reload();"><i class="fa fa-star"></i></a>');
    } else {
        document.write('<a href="#" title="Add to favorites" onclick="javascript:addStockID('+id+'); window.location.reload();"><i class="fa fa-star-o"></i></a>');
    }
}

function remStockID(id) {
    try {
        var json_str = getCookie('favorites');
        var arr = JSON.parse(json_str);
    } catch (e) {
        arr = [];
    }

    index = arr.indexOf(id);
    if (index >= 0) {
        arr.splice(index, 1);
    }

    var json_str = JSON.stringify(arr);
    setCookie('favorites', json_str, 14);
}

function addStockID(id) {
    try {
        var json_str = getCookie('favorites');
        var arr = JSON.parse(json_str);
    } catch (e) {
        arr = [];
    }

    arr.push(id);
    while (arr.length > 5) {
        arr.shift();
    }

    var json_str = JSON.stringify(arr);
    setCookie('favorites', json_str, 14);
}

// source: http://www.w3schools.com/js/js_cookies.asp
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

// source: http://www.w3schools.com/js/js_cookies.asp
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function extendCookie(cname) {
    var json_str = getCookie(cname);
    setCookie(cname, json_str, 14);
}
