if (!M.mod_kakiemon) {
	M.mod_kakiemon = {
		/**
		 * @memberOf M.mod_kakiemon
		 */
		page_view_init: function(Y, params) {
			this.Y = Y;
			this.params = params;

			var del = new Y.DD.Delegate({
				container: ".block-column",
				nodes: ".kaki-block",
				target: {
					padding: "0 0 0 20"
				}
			});
			del.dd.plug(Y.Plugin.DDProxy, {
				moveOnEnd: false,
				cloneNode: true
			});

			//イベント
			del.on("drag:drophit", function(e) {
				var drag = e.drag.get("node");
				var drop = e.drop.get("node");
				drop.get("parentNode").insertBefore(drag, drop);
				Y.io(M.cfg.wwwroot+"/mod/kakiemon/ajax.php", {
					data: {
						id: this.params.cmid,
						action: "blockmove",
						block: drag.getData("id"),
						target: drop.getData("id")
					}
				})
			}, this);
		}
	};
}
