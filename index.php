<?php
require_once 'includes/lights.php';
require_once 'includes/light.php';
require_once 'includes/scenes.php';
require_once 'includes/scene.php';

// Get our scenes.
$Scenes = new Scenes();
$Scenes->load();

// Get our lights.
$Lights = new Lights();

// Process any posted data (commands).
if ( $_SERVER['REQUEST_METHOD'] == 'POST'
  && !empty($_POST['action'])
) {
  switch($_POST['action']) {
    case 'power':
      // Validate input.
      if (empty($_POST['power']) || empty($_POST['light'])) {
        break;
      }

      // Set the light's power.
      if ($_POST['power'] == 'on') {
        $Lights->set_state([ 'on' => true ], $_POST['light']);
      } else {
        $Lights->set_state([ 'on' => false ], $_POST['light']);
      }
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
  <script src="assets/js/colorpicker.js"></script>

  <script src="assets/js/lights.js"></script>
</head>
<body>
  <div class="navbar navbar-default navbar-static-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">Lights</a>
      </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
          <li><a href="#home" class="js-page-home">Home</a></li>
          <li><a href="#scenes" class="js-page-scenes">Scenes</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="container" id="page-home">
    <?php
    foreach($Lights->lights as $light):
      $ct_class = $hs_class = '';
      $ct_active = $hs_active = false;
      $ct_active_data = $hs_active_data = 'false';
      if ($light->colormode == 'ct') {
        $ct_active = true;
        $ct_active_data = 'true';
        $ct_class = ' btn-primary';
      } elseif($light->colormode == 'hs') {
        $hs_active = true;
        $hs_active_data = 'true';
        $hs_class = ' btn-primary';

        $hue = floor(($light->hue * 255) / 65535);
        $sat = floor(($light->sat * 100) / 255);
        $bri = floor(($light->bri * 100) / 255);
      }
      ?>
      <div class="row">
        <div class="col-sm-3 light-name js-toggle-controls clickable" data-id="<?=$light->id?>">
          <span class="glyphicon glyphicon-cog"></span>
          <?=$light->name?>
        </div>
        <div class="col-sm-1 col-sm-offset-6">
          <div id="light_swatch_<?=$light->id?>" class="scene-swatch" style="background-color: <?=$light->as_hex()?>">&nbsp;</div>
        </div>
        <div class="col-sm-2" style="text-align: right; margin-bottom: 3px; margin-top: 3px;">
          <button
            style="width: 100%;"
            data-light-id="<?=$light->id?>"
            data-power="<?=($light->power)?'true':'false'?>"
            class="btn btn-default<?=($light->power)?' btn-success':''?> js-button-light"
          >Toggle</button>
        </div>
      </div>
      <div class="row" id="controls_<?=$light->id?>" style="display: none; margin-top: 4px;">
        <div class="col-sm-2 col-sm-offset-6 light-controls-type">
          <div class="btn-group btn-group-sm">
            <button
              data-mode="ct"
              data-active="<?=$ct_active_data?>"
              class="btn btn-default js-toggle-colormode<?=$ct_class?>"
            >Temp</button>
            <button
              data-mode="hs"
              data-active="<?=$hs_active_data?>"
              class="btn btn-default js-toggle-colormode<?=$hs_class?>"
            >Hue/Sat</button>
          </div>
        </div>
        <div class="col-sm-4" style="text-align: right;">
          <div class="light-controls">
            <div class="light-controls-ct" style="<?=($ct_active)?'':' display:none;'?>">
              <input
                style="width: 100%;"
                type="range" min="1" max="255"
                data-light-id="<?=$light->id?>"
                name="light_<?=$light->id?>_bri" value="<?=$light->bri?>"
                class="js-slider-bri" />
              <br/>
              <input
                style="width: 100%;"
                type="range" min="153" max="500"
                data-light-id="<?=$light->id?>"
                name="light_<?=$light->id?>_ct" value="<?=$light->ct?>"
                class="js-slider-ct" />
            </div>
            <div class="light-controls-hs" style="<?=($hs_active)?'':' display:none;'?>">
              <input
                type="color"
                data-light-id="<?=$light->id?>"
                data-light="light_<?=$light->id?>_hs"
                class="js-light-control-hs"
                name="light_<?=$light->id?>_hs"
                value="<?=$light->as_hex()?>">
            </div>
          </div>
        </div>
      </div>
    <?php
    endforeach; ?>

    <div class="col-sm-3" style="padding-right: 0; text-align: right; margin-bottom: 3px; margin-top: 3px;">
      <button
        style="width: 100%;"
        class="btn btn-info js-button-save-scene"
      >Save Current Settings as Scene</button>
    </div>
  </div>

  <div id="page-scenes" class="container" style="display: none;">
    <?php
    foreach($Scenes as $scene):
      ?>
      <div class="row">
        <div class="col-sm-3 scene-name" data-scene-id="<?=$scene->id?>">
          <span class="glyphicon glyphicon-cog clickable js-toggle-scene-controls"></span>
          <?=$scene->name?>
        </div>
        <div class="col-sm-2 col-sm-offset-5">
          <?php
          foreach($scene->lights as $light):
            ?>
            <div class="scene-swatch" style="background-color: <?=$light->as_hex()?>">&nbsp;</div>
            <?php
          endforeach;
          ?>
        </div>
        <div class="col-sm-2">
          <button
            style="width: 100%; margin-top: 3px; margin-bottom: 3px;"
            data-scene-id="<?=$scene->id?>"
            class="btn btn-primary js-button-scene"
          >Choose</button>
        </div>
      </div>
      <div id="scene_controls_<?=$scene->id?>" class="scene-controls" style="display: none;">
      <?php
      foreach ($scene->lights as $light):
        $ct_class = $hs_class = '';
        $ct_active = $hs_active = false;
        $ct_active_data = $hs_active_data = 'false';
        if ($light->colormode == 'ct') {
          $ct_active = true;
          $ct_active_data = 'true';
          $ct_class = ' btn-primary';
        } elseif($light->colormode == 'hs') {
          $hs_active = true;
          $hs_active_data = 'true';
          $hs_class = ' btn-primary';

          $hue = floor(($light->hue * 255) / 65535);
          $sat = floor(($light->sat * 100) / 255);
          $bri = floor(($light->bri * 100) / 255);
        }
        ?>
        <div class="row">
          <div class="col-sm-2 col-sm-offset-6 light-controls-type">
            <div class="btn-group btn-group-sm">
              <button
                data-mode="ct"
                data-active="<?=$ct_active_data?>"
                class="btn btn-default js-toggle-colormode<?=$ct_class?>"
              >Temp</button>
              <button
                data-mode="hs"
                data-active="<?=$hs_active_data?>"
                class="btn btn-default js-toggle-colormode<?=$hs_class?>"
              >Hue/Sat</button>
            </div>
          </div>
          <div class="col-sm-4" style="text-align: right;">
            <div class="light-controls">
              <div class="light-controls-ct" style="<?=($ct_active)?'':' display:none;'?>">
                <input
                  style="width: 100%;"
                  type="range" min="1" max="255"
                  data-scene-id="<?=$scene->id?>"
                  data-light-id="<?=$light->id?>"
                  name="scene_<?=$scene->id?>_light_<?=$light->id?>_bri" value="<?=$light->bri?>"
                  class="js-scene-slider-bri" />
                <br/>
                <input
                  style="width: 100%;"
                  type="range" min="153" max="500"
                  data-scene-id="<?=$scene->id?>"
                  data-light-id="<?=$light->id?>"
                  name="scene_<?=$scene->id?>_light_<?=$light->id?>_ct" value="<?=$light->ct?>"
                  class="js-scene-slider-ct" />
              </div>
              <div class="light-controls-hs" style="<?=($hs_active)?'':' display:none;'?>">
                <input
                  type="color"
                  data-scene-id="<?=$scene->id?>"
                  data-light-id="<?=$light->id?>"
                  class="js-scene-control-hs"
                  name="scene_<?=$scene->id?>_light_<?=$light->id?>_hs"
                  value="<?=$light->as_hex()?>">
              </div>
            </div>
          </div>
        </div>
        <?php
      endforeach;
      ?>
      </div>
      <?php
    endforeach;
    ?>
  </div>

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
</body>
</html>
