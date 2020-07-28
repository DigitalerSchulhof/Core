var core = {};

var $ = (s, arr) => {
  all = document.querySelectorAll(s);
	if(all.length === 1 && arr !== true) {
		return all[0];
	} else {
		return all;
	}
}