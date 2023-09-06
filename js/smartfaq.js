(function($) {
$(document).ready(function(){
$('.answer').hide();
$('.faq-body h2').click
(function(){
$(this).next().toggleClass('show');
var showClass= ($(this).next().attr('class'));
if(showClass.indexOf('show')!=-1){
$(this).next('.answer').fadeIn();
$(this).addClass('close');
}
else {
$(this).next('.answer').fadeOut();
$(this).removeClass('close');
}
return false;
});
});
})(jQuery);