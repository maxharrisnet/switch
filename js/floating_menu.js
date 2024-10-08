//config
$float_speed=1500; //milliseconds
$float_easing="easeOutQuint";
$menu_fade_speed=500; //milliseconds
$page_load_fade_delay=2000; //milliseconds
$closed_menu_opacity=1;

//cache vars
$fl_menu=$("#fl_menu");
$fl_menu_menu=$("#fl_menu .menu");
$fl_menu_label=$("#fl_menu .label2");
$fl_menu_item=$("#fl_menu .menu .menu_item");

$(window).load(function() {
	menuPosition=$fl_menu.position().top;
	FloatMenu();
	$fl_menu_item.delay($page_load_fade_delay).fadeTo($menu_fade_speed, 0, function(){$fl_menu_item.css("display","none");});
	$fl_menu.hover(
		function(){ //mouse over
			$fl_menu_label.stop().fadeTo($menu_fade_speed, 1);
			$fl_menu_item.css("display","block").stop().fadeTo($menu_fade_speed, 1);
		},
		function(){ //mouse out
			$fl_menu_label.stop().fadeTo($menu_fade_speed, $closed_menu_opacity, function(){$fl_menu_item.css("display","none");});
			$fl_menu_item.stop().fadeTo($menu_fade_speed, 0);
		}
	);
});

$(window).scroll(function () { 
	FloatMenu();
});

function FloatMenu(){
	var scrollAmount=$(document).scrollTop();
	var newPosition=menuPosition+scrollAmount;
	if($(window).height()<$fl_menu.height()+$fl_menu_menu.height()){
		$fl_menu.css("top",menuPosition);
	} else {
		$fl_menu.stop().animate({top: newPosition}, $float_speed, $float_easing);
	}
}