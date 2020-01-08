window._unic_start = true;
window.__cmp = window.__cmp || function () {
	window.__cmp.commandQueue = window.__cmp.commandQueue || [];
	window.__cmp.commandQueue.push(arguments);
};
window.__cmp.commandQueue = window.__cmp.commandQueue || [];
window.__cmp.receiveMessage = function (event) {
	var data = event && event.data && event.data.__cmpCall;

	if (data) {
		var callId = data.callId,
		    command = data.command,
		    parameter = data.parameter;

		window.__cmp.commandQueue.push({
			callId: callId,
			command: command,
			parameter: parameter,
			event: event
		});
	}
};
var listen = window.attachEvent || window.addEventListener;
var eventMethod = window.attachEvent ? "onmessage" : "message";
listen(eventMethod, function (event) {
	window.__cmp.receiveMessage(event);
}, false);
function addLocatorFrame() {
	if (!window.frames['__cmpLocator']) {
		if (document.body) {
			var frame = document.createElement('iframe');
			frame.style.display = 'none';
			frame.name = '__cmpLocator';
			document.body.appendChild(frame);
		} else {
			setTimeout(addLocatorFrame, 5);
		}
	}
}
addLocatorFrame();

if(window.__unic_config['unic_license']) {
	var license = window.__unic_config['unic_license'];
} else {
	var license = '69a3449348';
}

(function() {
    var st = document.createElement('script');
    st.type = 'text/javascript';
    st.async = true;
    st.src = 'https://cmp.uniconsent.com/t/'+license+'.cmp.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(st, s);
})();