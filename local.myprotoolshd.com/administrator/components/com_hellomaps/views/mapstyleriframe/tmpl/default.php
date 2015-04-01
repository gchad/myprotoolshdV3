<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
// load tooltip behavior
?>
<html>
  <head>
    <title>Google Maps API Styled Map Wizard</title>
    <link rel="stylesheet" type="text/css" href="<?php echo JURI::base().'components/com_hellomaps/assets/map_styler/'; ?>StyledMapWizard.css"></link>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&libraries=places&key=<?php echo $this->gmap_api_key; ?>"></script>
    <script type="text/javascript" src="<?php echo JURI::base().'components/com_hellomaps/assets/map_styler/'; ?>StyledMapWizard.js"></script>
  </head>
  <body onLoad="init()">
    <div id="map"></div>
    
    <div class="control" id="mapStyle">
      <div class="heading" id="mapStyleHeading">
        Map Style
        <input type="button" id="addStyle" value="Add" onClick="addStyle()"/>
      </div>
      <div id="mapStyleScrollable">
      </div>
      <div id="buttonPanel">
        <input id="jsonButton" type="button" value="Show JSON" onClick="showJson()" />
        <input id="staticMapButton" type="button" value="Static Map" onClick="showStaticMap()" />
        <input id="helpButton" type="button" value="Help" onClick="showHelp()" disabled />
      </div>
    </div>
    
    <div class="control" id="selectors">
      <div class="heading" id="selectorsHeading">
        Selectors
        <input type="button" id="resetStyle" value="Reset" onClick="resetCurrentStyle()" />
      </div>
      
      <div class="selectorHeading">
        Feature type
      </div>
      <div id="featureTypePanel" class="selector">
        <div id="featureLists">
          <select class="features" id="features_level_0" size="8">
            <option value="all" selected="selected" onClick="setFeatureType(0)">All</option>
          </select>
          <select class="features" id="features_level_1" size="8">
            <option value="administrative" onClick="setFeatureType(1)">Administrative</option>
            <option value="landscape" onClick="setFeatureType(1)">Landscape</option>
            <option value="poi" onClick="setFeatureType(1)">Point of interest</option>
            <option value="road" onClick="setFeatureType(1)">Road</option>
            <option value="transit" onClick="setFeatureType(1)">Transit</option>
            <option value="water" onClick="setFeatureType(1)">Water</option>
          </select>
          <select class="features" id="features_level_2" size="8">
          </select>
          <select class="features" id="features_level_3" size="8">
          </select>
        </div>
      </div>

      <div class="selectorHeading" id="elementHeading">
        Element type
      </div>
      <div id="elementTypePanel" class="selector">
        <div id="elementLists">
          <select class="elements" id="elements_root" size="8">
            <option value="all" selected="selected" onClick="setElementType(0)">All</option>
          </select>
          <select class="elements" id="elements_level_1" size="8">
            <option value="geometry" onClick="setElementType(1)">Geometry</option>
            <option value="labels" onClick="setElementType(1)">Labels</option>
          </select>
          <select class="elements" id="elements_level_2" size="8">
          </select>
          <select class="elements" id="elements_level_3" size="8">
          </select>
        </div>
      </div>
      
      <div class="selectorHeading" id="stylersHeading">
        Stylers
      </div>
      
      <div id="ruleColumn" class="selector">
        <div class="rule" id="visibilityRule">
          <table class="rulename">
            <tr>
              <td width="20"><input type="checkbox" id="set_visibility" onClick="setStyler('visibility')" /></td>
              <td width="111"><span class="clickable_label" onClick="do_click('visibility')"><b>Visibility</b></span></td>
              <td width="129" align="right">
                <input type="text" size="9" onFocus="blur()" id="visibility" class="ruleValue" />
              </td>
            </tr>
          </table>
          <div class="rulecontrol" id="visibilities">
            <input type="radio" name="visibility" id="visibility_on" value="on" onClick="setVisibility()"/>On
            <input type="radio" name="visibility" id="visibility_simplified" value="simplified" onClick="setVisibility()"/>Simplified
            <input type="radio" name="visibility" id="visibility_off" value="off" onClick="setVisibility()"/>Off
          </div>
        </div>
        <div class="rule" id="invertLightnessRule">
          <table class="rulename">
            <tr>
              <td width="20"><input type="checkbox" id="set_invert_lightness" onClick="setInvertLightness()" /></td>
              <td width="111"><span class="clickable_label" onClick="do_click('invert_lightness')"><b>Invert lightness</b></span></td>
              <td width="129" align="right">
                <input type="text" size="9" onFocus="blur()" id="invert_lightness" class="ruleValue" />
              </td>
            </tr>
          </table>
        </div>
        <div class="rule">
          <table class="rulename">
            <tr>
              <td width="20"><input type="checkbox" id="set_color" onClick="setStyler('color')" /></td>
              <td width="111"><span class="clickable_label" onClick="do_click('color')"><b><span id="colourLabel">Color</span></b></span></td>
              <td width="129" align="right">
                <input id="colorSample" disabled="disabled" />
                <input type="text" size="9" onFocus="setColor()" onKeyUp="setHexColor()" maxlength="7" id="color" class="ruleValue" />
              </td>
            </tr>
          </table>
          <div class="rulecontrol">
            <table id="colorSliders">
              <tr>  
              <td>R</td>
                <td><input class="colorSlider" type="range" id="redSlider" min="0" max="255" value="0" onChange="setColorRGB()" onFocus="blur()" /></td>
                <td><input class="colorInt" type="text" size="3" id="redInt" maxlength="3" onFocus="blur()" /></td>
              </tr>
              <tr>
                <td>G</td>
                <td><input class="colorSlider" type="range" id="greenSlider" min="0" max="255" value="0" onChange="setColorRGB()" onFocus="blur()" /></td>
                <td><input class="colorInt" type="text" size="3" id="greenInt"  maxlength="3" onFocus="blur()" /></td>
              </tr>
              <tr>
                <td>B</td>
                <td><input class="colorSlider" type="range" id="blueSlider" min="0" max="255" value="0" onChange="setColorRGB()" onFocus="blur()" ></td>
                <td><input class="colorInt" type="text" size="3" id="blueInt" maxlength="3" onFocus="blur()" /></td>                
              </tr>
            </table>
          </div>
        </div>
        <div class="rule">
          <table class="rulename">
            <tr>
              <td width="20"><input type="checkbox" id="set_weight" onClick="setStyler('weight')" /></td>
              <td width="111"><span class="clickable_label" onClick="do_click('weight')"><b>Weight</b></span></td>
              <td width="129" align="right">
                <input type="text" size="9" onFocus="blur()" id="weight" class="ruleValue" />
              </td>
            </tr>
          </table>
          <div class="rulecontrol">
            <input class="slider" type="range" id="weightSlider" min="1" max="80" value="0" onChange="setWeight()">
          </div>
        </div>
        <div class="rule">
          <table class="rulename">
            <tr>
              <td width="20"><input type="checkbox" id="set_hue" onClick="setStyler('hue')" /></td>
              <td width="111"><span class="clickable_label" onClick="do_click('hue')"><b>Hue</b></span></td>
              <td width="129" align="right">
                <input id="hueSample" disabled="disabled" />
                <input type="text" size="9" onFocus="blur()" id="hue" class="ruleValue" />
              </td>
            </tr>
          </table>
          <div class="rulecontrol">
            <div id="huePicker"></div>
          </div>
        </div>
        <div class="rule">
          <table class="rulename">
            <tr>
              <td width="20"><input type="checkbox" id="set_saturation" onClick="setStyler('saturation')" /></td>
              <td width="111"><span class="clickable_label" onClick="do_click('saturation')"><b>Saturation</b></span></td>
              <td width="129" align="right">
                <input type="text" size="9" onFocus="blur()" id="saturation" class="ruleValue" />
              </td>
            </tr>
          </table>
          <div class="rulecontrol">
            <input class="slider" type="range" id="satSlider" min="-100" max="100" value="0" onChange="setSaturation()">
          </div>
        </div>
        <div class="rule">
          <table class="rulename">
            <tr>
              <td width="20"><input type="checkbox" id="set_lightness" onClick="setStyler('lightness')" /></td>
              <td width="111"><span class="clickable_label" onClick="do_click('lightness')"><b>Lightness</b></span></td>
              <td width="129" align="right">
                <input type="text" size="9" onFocus="blur()" id="lightness" class="ruleValue" />
              </td>
            </tr>
          </table>
          <div class="rulecontrol">
            <input class="slider" type="range" id="lightSlider" min="-100" max="100" value="0" onChange="setLightness()">
          </div>
        </div>
        <div class="rule">
          <table class="rulename">
            <tr>
              <td width="20"><input type="checkbox" id="set_gamma" onClick="setStyler('gamma')" /></td>
              <td width="111"><span class="clickable_label" onClick="do_click('gamma')"><b>Gamma</b></span></td>
              <td width="129" align="right">
                <input type="text" size="9" onFocus="blur()" id="gamma" class="ruleValue" />
              </td>
            </tr>
          </table>
          <div class="rulecontrol">
            <input class="slider" type="range" id="gammaSlider" min="0" max="500" value="250" onChange="setGamma()">
          </div>
        </div>
      </div>
      
    </div>
    <div id="help" class="srcPanel">
      <img src="<?php echo JURI::base().'components/com_hellomaps/assets/map_styler/close.png'; ?>" class="closeIcon" onClick="closeHelp()" />
      <h1 class="srcTitle">How to use the Styled Maps Wizard</h1>
      <ol>
        <li>Navigate to the map region you wish to use to preview your style. Jump to a particular location using the <b>Enter a location</b> field in the top right.</li>
        <li>Select a type of feature to style in the <b>Selectors</b> panel.</li>
        <li>If you only wish to style the Geometry or Labels for the selected feature type, select the required Element Type.</li>
        <li>Select the combination of Stylers to apply to the selected feature type.</li>
        <li>Once you are happy with the styling of the feature type, click the <b>Add</b> button in the <b>Map Style</b> panel to save the style and create a new style to work on.</li>
        <li>Repeat Steps 2 to 5 to build up the set of styles for your map. Styles are applied in the order they are listed in the <b>Map Style</b> panel.</li>
        <li>Select an existing style in the <b>Map Style</b> panel to edit it.</li>
        <li>Delete a style by clicking on the trashcan icon to the right of the style number.</li>
        <li>When you are happy with your Styled Map, click the <b>Show JSON</b> button to display the JSON object to pass to the <code>style</code> property of your
        <a target="_new" href="http://code.google.com/apis/maps/documentation/javascript/reference.html#MapOptions">MapOptions</a> object in order to apply the style to a Maps API v3 Map.</li>
        <li>To generate an example map with this style using the <a href="http://code.google.com/apis/maps/documentation/staticmaps/">Static Maps API</a>, click the <b>Static Map</b> button.</li>
      </ol>
      <p>For more information on Map Styling, please see the <a target="_new" href="http://code.google.com/apis/maps/documentation/javascript/styling.html">Maps API v3 Documentation</a>,
      and <a target="_new" href="http://code.google.com/apis/maps/documentation/staticmaps/#StyledMaps">Static Maps API Documentation</a>.</p>
    </div>
    <div id="lightbox"></div>
    <div id="json" class="srcPanel"><img src="<?php echo JURI::base().'components/com_hellomaps/assets/map_styler/close.png'; ?>" class="closeIcon" onClick="closeJson()" /><h1 class="srcTitle">Google Maps API v3 Styled Maps JSON</h1></div>
    <div id="staticMap" class="srcPanel">
      <img src="<?php echo JURI::base().'components/com_hellomaps/assets/map_styler/close.png'; ?>" class="closeIcon" onClick="closeStaticMap()" />
      <div id="staticMapImgDiv">
        <h1 class="srcTitle">Styled Google Static Map</h1>
        <img id="staticMapImg"/>
        <table id="staticMapUrlTable"><tr><td><div id="staticMapUrl"></div></td></tr></table>
      </div>
    </div>
  </body>
</html>
<?php
exit;
?>
