if editing
    $ \.kaki-block .resizable do
        stop: (e, ui) ->
            $.get "#{M.cfg.wwwroot}/mod/kakiemon/ajax.php",
                action: \blockresize
                id: cmid
                block: $ e.target .data \id
                width: ui.size.width
                height: ui.size.height
