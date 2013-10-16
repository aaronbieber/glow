<?php
require_once 'includes/lights.php';
require_once 'includes/light.php';
require_once 'includes/scene.php';

$scenes = [];

$temp_scene = new Scene();
$temp_scene->name = 'Groovy';
$temp_scene->id = 1;
$temp_scene->lights = [
  new Light([
    'id' => 1,
    'colormode' => 'hs',
    'hue' => 47992,
    'sat' => 192,
    'bri' => 109
  ]),
  new Light([
    'id' => 2,
    'colormode' => 'hs',
    'hue' => 55115,
    'sat' => 167,
    'bri' => 116
  ]),
  new Light([
    'id' => 3,
    'colormode' => 'hs',
    'hue' => 47992,
    'sat' => 192,
    'bri' => 109
  ])
];
$scenes[$temp_scene->id] = $temp_scene;

$Lights = new Lights();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!empty($_POST['power'])) {
    if ($_POST['power'] == 'on') {
      $Lights->set_state([ 'on' => true ], $_POST['light']);
    } else {
      $Lights->set_state([ 'on' => false ], $_POST['light']);
    }
  }

  // If we are given a scene that exists, set it.
  if (!empty($_POST['scene']) && isset($scenes[$_POST['scene']])) {
    // Set the state of each light in the scene.
    $scene = $scenes[$_POST['scene']];
    foreach ($scene->lights as $light) {
      if ($light->colormode == 'ct') {
        $state = [
          'bri' => $light->bri,
          'ct'  => $light->ct
        ];
      } else {
        $state = [
          'hue' => $light->hue,
          'sat' => $light->sat,
          'bri' => $light->bri
        ];
      }
      $Lights->set_state($state, $light->id);
    }
  }

  if ( !empty($_POST['hue'])
    && !empty($_POST['sat'])
    && !empty($_POST['bri'])
  ) {
    $state = [
      'hue' => (int) $_POST['hue'],
      'sat' => (int) $_POST['sat'],
      'bri' => (int) $_POST['bri']
    ];
    $Lights->set_state($state, $_POST['light']);
  }

  if (!empty($_POST['bri'])) {
    $state = [ 'on' => true, 'bri' => (int) $_POST['bri'] ];
    $Lights->set_state($state, $_POST['light']);
  }

  if (!empty($_POST['ct'])) {
    $state = [ 'on' => true, 'ct' => (int) $_POST['ct'] ];
    $Lights->set_state($state, $_POST['light']);
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
      //$state = $light->state;
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
          <span
            class="glyphicon glyphicon-cog">
          </span>
          <?=$light->name?>
        </div>
        <div class="col-sm-2 col-sm-offset-7" style="text-align: right; margin-bottom: 3px; margin-top: 3px;">
          <button
            style="width: 100%;"
            data-light-id="<?=$light->id?>"
            data-power="<?=($light->on)?'true':'false'?>"
            class="btn btn-default<?=($light->on)?' btn-success':''?> js-button-light"
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
                name="light_<?=$light->id?>_bri" value="<?=$light->bri?>"
                class="js-slider-bri" />
              <br/>
              <input
                style="width: 100%;"
                type="range" min="153" max="500"
                name="light_<?=$light->id?>_ct" value="<?=$light->ct?>"
                class="js-slider-ct" />
            </div>
            <div class="light-controls-hs" style="<?=($hs_active)?'':' display:none;'?>">
              <input
                type="color"
                data-light-id="<?=$light->id?>"
                data-light="light_<?=$light->id?>_hs"
                class="light-control-hs"
                name="light_<?=$light->id?>_hs"
                value="<?=$light->as_hex()?>">
            </div>
          </div>
        </div>
      </div>
    <?php
    endforeach; ?>
  </div>

  <div id="page-scenes" class="container" style="display: none;">
    <?php
    foreach($scenes as $scene):
      ?>
      <div class="row">
        <div class="col-sm-3 scene-name"><?=$scene->name?></div>
        <div class="col-sm-2 col-sm-offset-5">
          <?php
          foreach($scene->lights as $light):
            ?>
            <div style="width: 20px; height: 20px; font-size: 1px; margin-left: 3px; border-radius: 3px; float: left; background-color: <?=$light->as_hex()?>">&nbsp;</div>
            <?php
          endforeach;
          ?>
        </div>
        <div class="col-sm-2">
          <button
            style="width: 100%;"
            data-scene-id="<?=$scene->id?>"
            class="btn btn-primary js-button-scene"
          >Choose</button>
        </div>
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
</body>
</html>
