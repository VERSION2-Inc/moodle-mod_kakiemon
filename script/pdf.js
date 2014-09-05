// Generated by LiveScript 1.2.0
(function(){
  var page, system, path, output;
  page = require('webpage').create();
  system = require('system');
  if (system.args.length < 3) {
    phantom.exit(1);
  }
  path = system.args[1];
  output = system.args[2];
  page.viewportSize = {
    width: 1200,
    height: 1200
  };
  page.open(path, function(status){
    return window.setTimeout(function(){
      page.render(output);
      return phantom.exit();
    }, 200);
  });
}).call(this);