$(function () {
    let $urlPathname = $(location).attr('pathname');
    let $lastElementUrl = $urlPathname.split('/').pop();
    $('ul.navbar-nav').children('[data-id="' + $lastElementUrl + '"]').addClass('active');
});