<?php
// Define the base library path for the application.
define('LIBRARY_PATH', '/var/www/glow/htdocs/includes');

// Pull in our autoloader, which is the only thing we need to include.
require_once 'vendor/autoload.php';
require_once 'includes/autoloader.php';

// Load the data for bootstrapping our models.
$scenes = new \AB\Chroma\Scenes();
$scenes->load();

$lights = new \AB\Chroma\Lights();
$lights->load();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="mobile-web-app-capable" content="yes">
  <title>Glow</title>

  <link rel="stylesheet" href="assets/css/bootstrap-3.3.4.min.css" />
  <link rel="stylesheet" href="assets/css/lights.css" />
  <link rel="stylesheet" href="assets/css/colorpicker.css" />
  <link rel="stylesheet" href="assets/css/farbtastic.css" />
  <link rel="stylesheet" href="assets/css/tipped.css" />
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
  <script src="assets/js/farbtastic.js" type="text/javascript"></script>
  <script src="assets/js/Sortable.js" type="text/javascript"></script>
  <script src="assets/js/tipped.js" type="text/javascript"></script>

  <script type="text/javascript">
   $(document).ready(function() {
     // Start everything
     app.sceneCollection = new app.SceneCollection(<?= json_encode($scenes->asArray()); ?>);
     app.lightCollection = new app.LightCollection(<?= json_encode($lights->asArray()); ?>);
     app.navigationLinkCollection = new app.NavigationLinkCollection([
       (new app.NavigationLink({ alias: 'scenes', name: 'Scenes', active: true })),
       (new app.NavigationLink({ alias: 'lights', name: 'Lights' }))
     ]);
     app.navigationView = new app.NavigationView({ collection: app.navigationLinkCollection });

     app.loadingToast = $('#loading');
     //app.appView = new app.AppView();
     app.router = new app.Router();
     Backbone.history.start();
   });
  </script>

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
