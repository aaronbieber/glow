<div class="container" id="page-home">
  <?php
  foreach($Lights->lights as $light) {
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
    include 'includes/page_blocks/home_light_row.php';
  }

  /* All lights row!
   *
   * The "All Lights" row should reflect the value of all lights only if they are the same, and some defaults otherwise.
   * What we do is snag all of the attributes of the first light by index, then loop over each of the remaining lights
   * comparing all of their values to those of the first light. As soon as any value differs, remove it from the final
   * state array, then use the final state array to draw the All Lights row.
   */
  $all_state = get_object_vars($Lights->lights[0]);

  $skip_first = true;
  foreach ($Lights->lights as $light) {
    // Skip the first light because it is the one we're comparing everything to.
    if ($skip_first) {
      $skip_first = false;
      continue;
    }

    /* Compare all other attributes. If the attribute is different, remove it from the final "state" array. Ultimately,
     * the "state" array will contain only the attributes that are identical across all lights.
     */
    foreach ($all_state as $attr => $value) {
      if ($all_state[$attr] != $light->{$attr}) {
        unset($all_state[$attr]);
      }
    }
  }

  // Synthesize the name for this light, which is displayed in the row.
  $all_state['name'] = 'All Lights';

  // Create a new light object using the values shared by all lights.
  $light = new Light($all_state);

  // Paint the row.
  include 'includes/page_blocks/home_light_row.php';
  ?>

  <div class="row">
    <div class="col-sm-3" style="margin-bottom: 3px; margin-top: 3px;">
      <button
        style="width: 100%;"
        class="btn btn-info js-button-save-scene"
      >Save Current Settings as Scene</button>
    </div>
  </div>
</div>
