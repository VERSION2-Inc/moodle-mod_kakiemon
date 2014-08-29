page = require \webpage .create!
require! system: \system

if system.args.length < 3
    phantom.exit 1

path = system.args[1]
output = system.args[2]

page.viewportSize =
    width: 1200
    height: 1200

page.open path, (status) ->
    window.setTimeout ->
        page.render output
        phantom.exit!
    , 200
