(function($){'use strict';$(document).ready(function(){$('.my-field-class #datepicker').parent().addClass('ddfwCalander');var cal_day_hide=$("#datepicker").attr('cal-day-hide');var day_delivery=$("#datepicker").attr('data-hide-day');if(cal_day_hide==null){}else{var disableddates=cal_day_hide.split(',');}
function DisableDates(date){var selectable=!isMonday(date)&&!isTuesday(date)&&!isWednesday(date)&&!isThursday(date)&&!isFriday(date)&&!isSaturday(date)&&!isSunday(date)&&!isDateDisabled(date);return[selectable];}
var custom_day_hide=$("#datepicker").attr('custom-day-hide');if(custom_day_hide==null){}else{var day_val=custom_day_hide.split(',');}
function isMonday(date){var day=date.getDay();if(day_val[0]==1){return day==1;}}
function isTuesday(date){var day=date.getDay();if(day_val[1]==2){return day==2;}}
function isWednesday(date){var day=date.getDay();if(day_val[2]==3){return day==3;}}
function isThursday(date){var day=date.getDay();if(day_val[3]==4){return day==4;}}
function isFriday(date){var day=date.getDay();if(day_val[4]==5){return day==5;}}
function isSaturday(date){var day=date.getDay();if(day_val[5]==6){return day==6;}}
function isSunday(date){var day=date.getDay();if(day_val[6]==7){return day==0;}}
function isDateDisabled(date){var m=date.getMonth()+1;var d=date.getDate();var y=date.getFullYear();if(d<10)d='0'+d;if(m<10)m='0'+m;var currentdate=d+'-'+m+'-'+y;if(typeof disableddates!="undefined"){return disableddates.indexOf(currentdate)>=0;}}
if(day_delivery==null){}else{$("#datepicker").datepicker({minDate:day_delivery,beforeShowDay:DisableDates,});}});})(jQuery);