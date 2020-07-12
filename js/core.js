var core = {};

var $ = (s) => {
	var all = document.querySelectorAll(s);
	if(all.length === 1) {
		return all[0];
	} else {
		return all;
	}
}