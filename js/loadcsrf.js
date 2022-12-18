
function getCookie(name) {
    var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}
var token_name=getCookie('token_name');
var user_csrf_token=getCookie(token_name);
function createHiddenInputElement() {
    var inputElement = document.createElement('input');
    inputElement.setAttribute('name', token_name);
    inputElement.setAttribute('class', token_name);
    inputElement.type = 'hidden';
    inputElement.value = user_csrf_token;
    return inputElement;
}
for (let elem of document.body.children) {
    if (elem.matches('form')) {
      elem.append(createHiddenInputElement());
    }
  }


