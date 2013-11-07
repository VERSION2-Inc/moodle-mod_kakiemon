YUI.add("moodle-mod_kakiemon-dragdrop", function(Y) {
	var DRAGDROP = function() {
		DRAGDROP.superclass.constructor.apply(this, arguments);
	};

	Y.extend(DRAGDROP, M.core.dragdrop, {

	});

	if (!M.mod_kakiemon) {
		M.mod_kakiemon = {};
	}
	M.mod_kakiemon.init_dragdrop = function(params) {
		return new DRAGDROP(params);
	};
}, "@VERSION@", {
	requires: ['moodle-core-dragdrop']
});
