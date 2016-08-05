<?php
/**
 * @file
 * open_data_federal_extras.features.features_overrides.inc
 */

/**
 * Implements hook_features_override_default_overrides().
 */
function open_data_federal_extras_features_override_default_overrides() {
  // This code is only used for UI in features. Exported alters hooks do the magic.
  $overrides = array();

  // Exported overrides for: field_base
  $overrides["field_base.field_frequency.settings|allowed_values|0"] = 'Daily';
  $overrides["field_base.field_frequency.settings|allowed_values|1"] = 'Weekly';
  $overrides["field_base.field_frequency.settings|allowed_values|2"] = 'Monthly';
  $overrides["field_base.field_frequency.settings|allowed_values|3"] = 'Annually';
  $overrides["field_base.field_frequency.settings|allowed_values|4"] = 'Continuously';
  $overrides["field_base.field_frequency.settings|allowed_values|5"] = 'Irregularly';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P0.33M"] = 'Three times a month - R/P0.33M';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P0.33W"] = 'Three times a week - R/P0.33W';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P0.5M"] = 'Semimonthly - R/P0.5M';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P0.5W"] = 'Biweekly - R/P0.5W';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P10Y"] = 'Decennial - R/P10Y';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P1D"] = 'Daily - R/P1D';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P1M"] = 'Monthly - R/P1M';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P1W"] = 'Weekly - R/P1W';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P1Y"] = 'Annual - R/P1Y';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P2M "] = 'Bimonthly - R/P2M';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P2W"] = 'Biweekly - R/P2W';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P2Y"] = 'Biennial - R/P2Y';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P3.5D"] = 'Semiweekly - R/P3.5D';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P3M"] = 'Quarterly - R/P3M';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P3Y"] = 'Triennial - R/P3Y';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P4M"] = 'Three times a year - R/P4M';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P4Y"] = 'Quadrennial -  R/P4Y';
  $overrides["field_base.field_frequency.settings|allowed_values|R/P6M"] = 'Semiannual - R/P6M';
  $overrides["field_base.field_frequency.settings|allowed_values|R/PT1S"] = 'Continuously updated - R/PT1S';

 return $overrides;
}
