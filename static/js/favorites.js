function addStockID(id) {
    console.log(id);;

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
    setCookie('favorites', json_str);
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
