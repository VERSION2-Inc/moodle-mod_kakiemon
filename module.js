M.mod_kakiemon = M.mod_kakiemon || {};

M.mod_kakiemon.page_view_init = function(Y, params) {
	this.Y = Y;
	this.params = params;

	if (this.params.editing) {
		this.page_view_init_block_dragdrop();
	}
};

M.mod_kakiemon.page_view_init_block_dragdrop = function() {
	var Y = this.Y;
	var CSS = {
		BLOCK: 'kaki-block',
		COLUMN_BLOCKS: 'block-column-blocks'
	};
	var goingUp = false;
	var lastY = 0;
	var neworder = 0;

	Y.DD.DDM.on('drop:over', function(e) {
		var drag = e.drag.get('node');
		var drop = e.drop.get('node');

		if (drop.get('className').indexOf(CSS.BLOCK) != -1) {
			if (!goingUp) {
				drop = drop.get('nextSibling');
			}
			drop.get('parentNode').insertBefore(drag, drop);
			e.drop.sizeShim();
			neworder = drop.getData('order');
		}
	});

	Y.DD.DDM.on('drag:start', function(e) {
		var drag = e.target;

		neworder = 0;

		drag.get('node').setStyle('opacity', '.25');
		drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
		drag.get('dragNode').setStyles({
			opacity: '.5',
			borderColor: drag.get('node').getStyle('borderColor'),
			backgroundColor: drag.get('node').getStyle('backgroundColor')
		});
	});

	Y.DD.DDM.on('drag:drag', function(e) {
		var y = e.target.lastXY[1];
		if (y < lastY) {
			goingUp = true;
		} else {
			goingUp = false;
		}
		lastY = y;
	});

	Y.DD.DDM.on('drag:end', function(e) {
		var drag = e.target;

		drag.get('node').setStyles({
			visibility: '',
			opacity: '1'
		});
	});

	Y.DD.DDM.on('drag:drophit', function(e) {
		var drag = e.drag.get('node');
		var drop = e.drop.get('node');

		if (drop.get('className').indexOf(CSS.BLOCK) == -1) {
			if (!drop.contains(drag)) {
				drop.appendChild(drag);
				neworder = 99;
			}
		}

		Y.io(M.cfg.wwwroot+"/mod/kakiemon/ajax.php", {
			data: {
				id: this.params.cmid,
				action: "blockmove",
				block: drag.getData("id"),
				column: drop.getData("column"),
				order: neworder
			}
		});
	}, this);

	var blocks = Y.all('.'+CSS.BLOCK);
	blocks.each(function(v, k) {
		new Y.DD.Drag({
			node: v,
			target: {
				padding: '0 0 0 20'
			}
		}).plug(Y.Plugin.DDProxy, {
			moveOnEnd: false
		});
	});
	var columns = Y.all('.'+CSS.COLUMN_BLOCKS);
	columns.each(function(v, k) {
		new Y.DD.Drop({
			node: v
		});
	});
};
