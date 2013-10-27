<?php
// Get our scenes.
$Scenes = new \AB\Chroma\Scenes();
$Scenes->load();

// Get our lights.
$Lights = new \AB\Chroma\Lights();

// Process any posted data (commands).
if ( $_SERVER['REQUEST_METHOD'] == 'POST'
  && !empty($_POST['action'])
) {
  switch($_POST['action']) {
    case 'power':
      // Validate input.
      if (!isset($_POST['power']) || !isset($_POST['light'])) {
        break;
      }

      if ($_POST['power'] == 'on') {
        $Lights->set_state([ 'on' => true ], $_POST['light']);
      } else {
        $Lights->set_state([ 'on' => false ], $_POST['light']);
      }
      break;

    case 'rename-scene':
      $Scenes->scenes[$_POST['scene']]->name = trim($_POST['name']);
      $Scenes->save();

      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'scene'   => (int) $_POST['scene'],
        'name'    => $_POST['name']
      ]);
      break;

    case 'select-scene':
      $scene = $Scenes->scenes[$_POST['scene']];
      foreach ($scene->lights as $light) {
        if ($light->power == false) {
          $state = [
            'power' => false
          ];
        } elseif ($light->colormode == 'ct') {
          $state = [
            'power'  => true,
            'bri' => $light->bri,
            'ct'  => $light->ct
          ];
        } elseif ($light->colormode == 'hs') {
          $state = [
            'power'  => true,
            'hue' => $light->hue,
            'sat' => $light->sat,
            'bri' => $light->bri
          ];
        }
        $Lights->set_state($state, $light->id);
      }
      break;

    case 'create-scene':
      // What is our new scene ID?
      $scene_id = max(array_keys($Scenes->scenes));
      $scene_id++;

      $Scenes->scenes[$scene_id] = new Scene();
      $Scenes->scenes[$scene_id]->name = 'Untitled Scene';
      foreach ($Lights->lights as $light) {
        $Scenes->scenes[$scene_id]->lights[$light->id] = $light;
      }

      $Scenes->save();
      break;

    case 'update-scene':
      // Validate input.

      $scene_id = $_POST['scene'];
      $light_id = $_POST['light'];

      $settings = ['power', 'bri', 'ct', 'hue', 'sat', 'ct'];
      foreach ($settings as $setting) {
        if (!empty($_POST[$setting])) {
          if ($setting == 'power') {
            $value = (bool) $_POST[$setting];
          } else {
            $value = (int) $_POST[$setting];
          }
          $Scenes->scenes[$scene_id]->lights[$light_id]->{$setting} = $value;
        }
      }

      $Scenes->save();
      break;

    case 'update-hsl':
      // Validate input.
      if ( empty($_POST['hue'])
        || empty($_POST['sat'])
        || empty($_POST['bri'])
      ) {
        break;
      }

      // Set the light's HSL values.
      $state = [
        'hue' => (int) $_POST['hue'],
        'sat' => (int) $_POST['sat'],
        'bri' => (int) $_POST['bri']
      ];

      $Lights->set_state($state, $_POST['light']);
      break;

    case 'update-ct':
      // Validate input.
      if (empty($_POST['ct'])) {
        break;
      }

      // Set the light's color temperature.
      $state = [ 'on' => true, 'ct' => (int) $_POST['ct'] ];
      $Lights->set_state($state, $_POST['light']);
      break;

    case 'update-bri':
      // Validate input.
      if (empty($_POST['bri'])) {
        break;
      }

      // Set the light's brightness.
      $state = [ 'on' => true, 'bri' => (int) $_POST['bri'] ];
      $Lights->set_state($state, $_POST['light']);
      break;
  }

  die();
} elseif ( $_SERVER['REQUEST_METHOD'] == 'GET'
        && !empty($_GET['status'])
) {
  header('Content-Type: application/json');
  echo json_encode($Lights->as_array());
  die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Lights</title>

  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/lights.css">
  <link rel="stylesheet" href="assets/css/colorpicker.css">

  <script src="//code.jquery.com/jquery.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script src="assets/js/underscore-min.js"></script>
</head>
<body>

  <?php
  include 'includes/page_blocks/navbar.php';
  ?>

  <?php
  include 'includes/page_blocks/home.php';
  include 'includes/page_blocks/scenes.php';
  ?>

  <div class="container">
    <div class="row">
      <div class="col-md-12 response-row">
        <a href="#" class="js-toggle-response toggle-response-row btn btn-default">Toggle response</a>
        <pre id="response" style="display: none;"><? var_dump($Lights->lights); ?></pre>
      </div>
    </div>
  </div>

  <div id="loading" style="display: none;">
    <img src="assets/images/ajax-loader.gif" />
    Working...
  </div>

  <script src="assets/js/colorpicker.js"></script>
  <script src="assets/js/lights.js"></script>
</body>
</html>
