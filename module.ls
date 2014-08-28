M.mod_kakiemon ?= {}

M.mod_kakiemon.page_view_init = (@Y, @params) ->
    Y = @Y

    @ajaxurl = "#{M.cfg.wwwroot}/mod/kakiemon/ajax.php"

    if @params.editing
        @page_view_init_block_dragdrop!

    Y.one '#showfeedbackform a' .on \click, ->
        Y.one \#feedbackform .toggleView!
        tinyMCE.activeEditor.focus!
    Y.one \#feedbackform .hide!

M.mod_kakiemon.page_view_init_block_dragdrop = ->
    Y = @Y
    CSS =
        BLOCK: \kaki-block
        COLUMN_BLOCKS: \block-column-blocks
        NEWBLOCK: \newblock
    goingUp = no
    lastY = 0
    neworder = 0
    Y.DD.DDM.on \drop:over, (e) ->
        drag = e.drag.get \node
        drop = e.drop.get \node
        if drop.hasClass CSS.BLOCK
            if goingUp
                drop = drop.get \nextSibling
            drop.get \parentNode .insertBefore drag, drop
            e.drop.sizeShim!
            neworder = drop.getData \order
        else if drop.hasClass \newblock
            blockedit.show!
    Y.DD.DDM.on \drag:start, (e) ->
        drag = e.target
        if drag.get \node .hasClass CSS.BLOCK
            neworder = 0
        drag.get \node .setStyle \opacity \.25
        drag.get \dragNode .set \innerHTML, (drag.get \node .get \innerHTML)
        drag.get \dragNode .setStyles do
            opacity: \.5
            borderColor: drag.get \node .getStyle \borderColor
            backgroundColor: drag.get \node .getStyle \backgroundColor
        Y.all \.block-column-blocks .setStyles do
            background: \#f8f8f8
            margin: \10px
    Y.DD.DDM.on \drag:drag, (e) ->
        drag = e.target
        if drag.get \node .hasClass CSS.BLOCK
            y = e.target.lastXY[1]
            goingUp = y < lastY
            lastY = y
    Y.DD.DDM.on \drag:end, (e) ->
        drag = e.target
        drag.get \node .setStyles do
            visibility: ''
            opacity: '1'
        Y.all \.block-column-blocks .setStyles do
            background: ''
            margin: '0'
    Y.DD.DDM.on \drag:drophit, (e) ->
        drag = e.drag.get \node
        drop = e.drop.get \node
        if !drop.contains drag
            drop.appendChild drag
            neworder = 99
        @ajax do
            action: \blockmove
            block: drag.getData \id
            column: drop.getData \column
            order: neworder
    , @
    Y.all ".#{CSS.BLOCK}" .each (v, k) ->
        new Y.DD.Drag do
            node: v
            target:
                padding: '0 0 0 20'
        .plug Y.Plugin.DDProxy,
            moveOnEnd: no
    Y.all ".#{CSS.COLUMN_BLOCKS}" .each (v, k) ->
        new Y.DD.Drop do
            node: v

M.mod_kakiemon.ajax = (data) ->
    Y = @Y
    data.id = @params.cmid
    Y.io @ajaxurl,
        data: data
    , @

