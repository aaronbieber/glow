<div id="page-scenes" class="container" style="display: none;">
  <?php
  foreach($Scenes as $scene):
    ?>
    <div class="scene-row">
      <div class="row">
        <div class="col-xs-6 scene-name">
          <span class="glyphicon glyphicon-chevron-right js-toggle-scene-controls clickable" data-scene-id="<?=$scene->id?>"></span>
          <span id="scene_name_label_<?=$scene->id?>"><?=$scene->name?></span>
        </div>
        <div class="col-xs-6" style="text-align: right;">
          <button
            style="float: right;"
            data-scene-id="<?=$scene->id?>"
            class="btn btn-primary js-button-scene scene-button"
          >Choose</button>

          <?php
          // Reverse the array because the floated elements will appear backwards.
          foreach(array_reverse($scene->lights) as $light):
            ?>
            <span class="color-swatch swatch-sm" style="float: right; background-color: <?=$light->as_hex()?>">&nbsp;</span>
            <?php
          endforeach;
          ?>
        </div>
      </div>

      <?php // Scene controls: ct/hsl switches, sliders, pickers. ?>
      <div id="scene_controls_<?=$scene->id?>" class="scene-controls" style="display: none;">
        <div class="row">
          <div class="col-xs-12">
            <div style="float: right;">
              <input type="text" id="scene_name_input_<?=$scene->id?>" value="<?=$scene->name?>" />
              <button
                class="btn btn-default btn-sm js-button-save-scene-name"
                data-scene-id="<?=$scene->id?>"
              ><span class="glyphicon glyphicon-floppy-disk save-scene-name-button clickable"></span></button>
            </div>
          </div>
        </div>
        <?php
        foreach ($scene->lights as $light):
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
          ?>
          <div class="row">
            <div class="col-xs-12 light-controls-type">
              <div class="light-controls">
                <div class="light-controls-ct" style="<?=($ct_active)?'':' display: none;'?>">
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
                <div class="light-controls-hs" style="<?=($hs_active)?'':' display: none;'?>">
                  <input
                    style="width: 100%;"
                    type="color"
                    data-scene-id="<?=$scene->id?>"
                    data-light-id="<?=$light->id?>"
                    class="js-scene-control-hs"
                    name="scene_<?=$scene->id?>_light_<?=$light->id?>_hs"
                    value="<?=$light->as_hex()?>">
                </div>
              </div>

              <div class="btn-group btn-group-xs" style="float: right; margin-right: 3px;">
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
          </div>
          <?php
        endforeach;
        ?>
      </div>
    </div>
    <?php
  endforeach;
  ?>
</div>
