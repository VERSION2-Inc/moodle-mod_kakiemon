if (M.mod_kakiemon == null) {
  M.mod_kakiemon = {};
}

M.mod_kakiemon.page_view_init = function(Y, params) {
  this.Y = Y;
  this.params = params;
  Y = this.Y;
  this.ajaxurl = M.cfg.wwwroot + '/mod/kakiemon/ajax.php';
  if (this.params.editing) {
    this.page_view_init_block_dragdrop();
  }
  Y.on('click', function(e) {
    Y.one('#feedbackform').toggleView();
    return tinyMCE.activeEditor.focus();
  }, '#showfeedbackform a');
  return Y.one('#feedbackform').hide();
};

M.mod_kakiemon.page_view_init_block_dragdrop = function() {
  var CSS, Y, blocks, columns, goingUp, lastY, neworder;
  Y = this.Y;
  CSS = {
    BLOCK: 'kaki-block',
    COLUMN_BLOCKS: 'block-column-blocks',
    NEWBLOCK: 'newblock'
  };
  goingUp = false;
  lastY = 0;
  neworder = 0;
  Y.DD.DDM.on('drop:over', function(e) {
    var drag, drop;
    drag = e.drag.get('node');
    drop = e.drop.get('node');
    if (drop.hasClass(CSS.BLOCK)) {
      if (goingUp) {
        drop = drop.get('nextSibling');
      }
      drop.get('parentNode').insertBefore(drag, drop);
      e.drop.sizeShim();
      return neworder = drop.getData('order');
    } else if (drop.hasClass('newblock')) {
      return blockedit.show();
    }
  });
  Y.DD.DDM.on('drag:start', function(e) {
    var drag;
    drag = e.target;
    if (drag.get('node').hasClass(CSS.BLOCK)) {
      neworder = 0;
    }
    drag.get('node').setStyle('opacity', '.25');
    drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
    return drag.get('dragNode').setStyles({
      opacity: '.5',
      borderColor: drag.get('node').getStyle('borderColor'),
      backgroundColor: drag.get('node').getStyle('backgroundColor')
    });
  });
  Y.DD.DDM.on('drag:drag', function(e) {
    var drag, y;
    drag = e.target;
    if (drag.get('node').hasClass(CSS.BLOCK)) {
      y = e.target.lastXY[1];
      goingUp = y < lastY;
      return lastY = y;
    }
  });
  Y.DD.DDM.on('drag:end', function(e) {
    var drag;
    drag = e.target;
    if (true) {
      return drag.get('node').setStyles({
        visibility: '',
        opacity: '1'
      });
    }
  });
  Y.DD.DDM.on('drag:drophit', function(e) {
    var drag, drop;
    drag = e.drag.get('node');
    drop = e.drop.get('node');
    if (true) {
      if (!drop.contains(drag)) {
        drop.appendChild(drag);
        neworder = 99;
      }
      return this.ajax({
        action: 'blockmove',
        block: drag.getData('id'),
        column: drop.getData('column'),
        order: neworder
      });
    }
  });
  blocks = Y.all('.' + CSS.BLOCK);
  blocks.each(function(v, k) {
    return new Y.DD.Drag({
      node: v,
      target: {
        padding: '0 0 0 20'
      }
    }).plug(Y.Plugin.DDProxy, {
      moveOnEnd: false
    });
  });
  columns = Y.all('.' + CSS.COLUMN_BLOCKS);
  return columns.each(function(v, k) {
    return new Y.DD.Drop({
      node: v
    });
  });
};

M.mod_kakiemon.ajax = function(data) {
  var Y;
  Y = this.Y;
  data.id = this.params.cmid;
  return Y.io(this.ajaxurl, {
    data: data
  }, this);
};
