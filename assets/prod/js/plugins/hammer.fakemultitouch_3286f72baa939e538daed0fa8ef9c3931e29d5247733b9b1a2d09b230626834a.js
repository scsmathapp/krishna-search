(function(c){c.plugins.fakeMultitouch=function(){var b=!1;c.HAS_POINTEREVENTS=navigator.msPointerEnabled&&navigator.msMaxTouchPoints&&1<=navigator.msMaxTouchPoints;c.event.getTouchList=function(a,d){if(c.HAS_POINTEREVENTS)return c.PointerEvent.getTouchList();if(a.touches)return a.touches;d==c.EVENT_START&&(b=!1);if(a.shiftKey){b||(b={pageX:a.pageX,pageY:a.pageY});d=b.pageX-a.pageX;var e=b.pageY-a.pageY;return[{identifier:1,pageX:b.pageX-d-50,pageY:b.pageY-e- -50,target:a.target},{identifier:2,pageX:b.pageX+
d- -50,pageY:b.pageY+e-50,target:a.target}]}b=!1;return[{identifier:1,pageX:a.pageX,pageY:a.pageY,target:a.target}]}}})(window.Hammer);