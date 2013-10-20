<div class="container" id="page-home">
  <?php
  foreach($Lights->lights as $light):
    $ct_class = $hs_class = ' btn-default';
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

    // Main light row: name, swatch, toggle.
    ?>
    <div class="light-row">
      <div class="row">
        <div class="col-xs-6 light-name">
          <span class="glyphicon glyphicon-chevron-right js-toggle-controls clickable" data-light-id="<?=$light->id?>"></span>
          <?=$light->name?>
        </div>
        <div class="col-xs-6" style="text-align: right;">
          <button
            style="float: right;"
            data-light-id="<?=$light->id?>"
            data-power="<?=($light->power)?'true':'false'?>"
            class="btn btn-default<?=($light->power)?' btn-success':''?> js-button-light light-button"
          >Toggle</button>

          <span id="light_swatch_<?=$light->id?>" class="color-swatch" style="float: right; background-color: <?=$light->as_hex()?>">&nbsp;</span>
        </div>
      </div>

      <?php // Light controls row: ct/hsl switch, sliders and pickers. ?>
      <div class="row" id="controls_<?=$light->id?>" style="display: none; margin-top: 4px;">
        <div class="col-xs-12">
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
                style="width: 100%;"
                data-light-id="<?=$light->id?>"
                data-light="light_<?=$light->id?>_hs"
                class="js-light-control-hs"
                name="light_<?=$light->id?>_hs"
                value="<?=$light->as_hex()?>">
            </div>
          </div>

          <div class="btn-group btn-group-xs" style="float: right; margin-right: 3px;">
            <button
              type="button"
              data-mode="ct"
              data-active="<?=$ct_active_data?>"
              class="btn js-toggle-colormode<?=$ct_class?>"
            >Temp</button>
            <button
              type="button"
              data-mode="hs"
              data-active="<?=$hs_active_data?>"
              class="btn js-toggle-colormode<?=$hs_class?>"
            >Hue/Sat</button>
          </div>
        </div>
      </div>
    </div>
  <?php
  endforeach; ?>

  <div class="row">
    <div class="col-sm-3" style="margin-bottom: 3px; margin-top: 3px;">
      <button
        style="width: 100%;"
        class="btn btn-info js-button-save-scene"
      >Save Current Settings as Scene</button>
    </div>
  </div>
</div>
