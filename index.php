<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="mobile-web-app-capable" content="yes">
  <title>Glow</title>

  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/lights.css">
  <link rel="stylesheet" href="assets/css/colorpicker.css">
  <link rel="shortcut icon" type="image/x-icon" href="/favicon.png" />
</head>
<body>
  <div id="navigation" class="navigation">
  </div>

  <div id="container" class="container">
  </div>

  <div id="loading" style="display: none;">
    <img src="assets/images/ajax-loader.gif" />
    Working...
  </div>

  <script src="assets/js/jquery-2.1.3.min.js" type="text/javascript"></script>
  <script src="assets/js/underscore-min.js"></script>
  <script src="assets/js/backbone-min.js" type="text/javascript"></script>
  <script src="assets/js/mustache.min.js" type="text/javascript"></script>
  <script src="assets/js/glow.js" type="text/javascript"></script>

  <?php
  // Rip in some templates like a boss.
  foreach (scandir('/var/www/glow/htdocs/assets/templates') as $template_file) {
    if ($template_file == '.' || $template_file == '..') continue;

    $template = file_get_contents('/var/www/glow/htdocs/assets/templates/' . $template_file);
    $template_name = substr($template_file, 0, strlen($template_file) - 9); //Remove .mustache
    echo sprintf("<script id=\"t-%s\" type=\"x-tmpl-mustache\">\n%s\n</script>\n\n", $template_name, $template);
  }
  ?>
</body>
</html>
