<?php

// Minimal oven efficiency, percentage.
CONST MIN_EFFICIENCY = 0.78;

// Lower heating value of wood, KW*h/kg
CONST HA = 4.16;

// Theoretical air volume. Nm3/kg.
CONST THEORETICAL_AIR_VOLUME = 4;

// Access air factor.
CONST ACCESS_AIR_FACTOR = 2.95;

// Theoretical flue gas volume Nm3/kg (normal cubic meter).
CONST THEORETICAL_FLUE_GAS_VOLUME = 4.8;

// Air density, kg/Nm3 (normal cubic meter).
CONST AIR_DENSITY = 1.293;

// Flue gas density. kg/Nm3 (normal cubic meter).
CONST FLUE_GAS_DENSITY = 1.282;

// Optimal firing period, minutes.
CONST OPTIMAL_FIRING_PERIOD = 77;

// Max CO exhaust, mg/Nm3.
CONST MAX_CO_EMISSION = 1500;

// MAX NOx mg/Nm3 (normal cubic meter).
CONST MAX_NOX_EMISSION = 225;

// Max soot emission, mg/Nm3 (normal cubic meter).
CONST MAX_SOOT_EMISSION = 120;

// Max particulate emission, mg/Nm3 (normal cubic meter).
CONST MAX_PARTICULATE_EMISSION = 90; 

// The allowed max area of glass.
CONST MAX_GLASS_RATIO = 1/6;


// Minimal side length of the combustion chamber, cm.
CONST MIN_COMBUSTION_CHAMBER_SIDE = 23;

$has_cooking_plate = FALSE;
$has_water_exchange = FALSE;
// Air intake height from the floor of the combustion chamber, cm.
$min_air_intage_height = 5;

function calculateAll(float $power, float $heating_period, $combustion_chamber_circumference, $single_wall = TRUE) {

    // M'Fa, in kg.
    $max_wood = calculateMaxWood($power, $heating_period);
    $min_wood = $max_wood * 0.5;

    // With surface in contact with the flames or flue.
    $minimal_oven_mass = $max_wood * 50;
    // In kg.

    
    // OTt. In cm2.
    $combustion_chamber_surface_requirement = calculateCombustionChamberSurfaceRequirement($max_wood);

    // In cm2.
    $minimal_combustion_chamber_area = calculateMinCombustionChamberArea($max_wood);

    // Optimal height of the combustion chamber, in cm.
    $combustion_chamber_optimal_height = calculateCombustionChamberOptimalHeight($max_wood, $combustion_chamber_circumference);
    // hTtmin
    $combustion_chamber_minimal_height = calculateCombustionChamberMinimalHeight($max_wood);
    $combustion_chamber_max_area = calculateCombustionChamberMaxArea($max_wood, $combustion_chamber_circumference);
    $combustion_chamber_side_length = (-2 * $combustion_chamber_minimal_height + sqrt((2 * $combustion_chamber_minimal_height) ** 2 + 1800 * $max_wood)) / 2;
    $minimal_passage_length = ($single_wall ? 1.3 : 1.5) * sqrt($max_wood);
    // Gázslicc / Biztonsági átégő / Gas groove (cm2).
    $pilot_burner_area = $max_wood;

    // In Celsius.
    $temperature = 23;
    // Altitude above sea level in m.
    $altitude = 100;
    $temp_correction_factor = calculateTempCorrectionFactor($temperature); 
    $altitude_correction_factor = calculateAltCorrectionFactor($altitude);
    $air_intake_flow_rate = calculateAirIntakeFlowRate($max_wood, $temperature, $altitude);
    $flue_gas_flow_rate = calculateFlueGasFlowRate($max_wood, $temperature, $altitude);
    $flue_gas_mass_flow_rate = 0.0035 * $max_wood;
    $real_air_density = calculateRealDensity(AIR_DENSITY, $temperature, $altitude);
    $real_flue_density = calculateRealDensity(FLUE_GAS_DENSITY, $temperature, $altitude);
}

/**
 * Calculates the flue gas flow rate.
 * 
 * @param float $max_wood
 *   The max wood weight (kg).
 * @param float $temp
 *   The temperature.
 * @param float $alt
 *   The altitude.
 * 
 * @return float
 *   The flue gas flow rate.
 */
function calculateflueGasFlowRate(float $max_wood, float $temp, float $alt) {
  return 0.00273 * $max_wood * calculateTempCorrectionFactor($temp) * calculateAltCorrectionFactor($alt);
}

/**
 * Calculates the Air intake flow rate.
 * 
 * @param float $max_wood
 *   The max wood weight (kg).
 * @param float $temp
 *   The temperature.
 * @param float $alt
 *   The altitude
 * 
 * @return float
 *   The air intake flow rate.
 */
function calculateAirIntakeFlowRate(float $max_wood, float $temp, float $alt) {
  return 0.00256 * $max_wood * calculateTempCorrectionFactor($temp) * calculateAltCorrectionFactor($alt);
}

/**
 * Calculates the minimal height of the combustion chamber.
 * 
 * @param float $max_wood
 *   The max wood weight (kg).
 * 
 * @return float
 *   The minimal height of the combustion chamber (cm).
 */
function calculateCombustionChamberMinimalHeight(float $max_wood) {
  return $max_wood + 25;
}

/**
 * Calculates the max area of the combustion chamber.
 * 
 * @param float $max_wood
 *   The max wood weight.
 * @param float $circumference
 * 
 * @return float
 *   The max area of the combustion chamber (cm2).
 */
function calculateCombustionChamberMaxArea(float $max_wood, float $circumference) {
  return 900 * $max_wood - calculateCombustionChamberMinimalHeight($max_wood) * $circumference / 2;
}

/**
 * Calculates the optimal height of the combustion chamber.
 * 
 * @param float $max_wood
 *   The max wood weight.
 * @param float $circumference
 *   The circumference of the combustion chamber.
 * 
 * @return float
 *   The optimal height of the combustion chamber (cm).
 */
function calculateCombustionChamberOptimalHeight(float $max_wood, float $circumference) {
  return (900 * $max_wood - 2 * calculateMinCombustionChamberArea($max_wood)) / $circumference;
}

/**
 * Calculates the minimum area of the combustion chamber (cm2).
 * 
 * @param float $max_wood
 *   The max wood weight (kg).
 * 
 * @return float
 *   The minimum area in cm2.
 */
function calculateMinCombustionChamberArea(float $max_wood) {
  return 100 * $max_wood;
}

/**
 * Calculates the required surface of the combustion chamber.
 * 
 * @param float $max_wood
 *   The max wood weight (kg).
 * 
 * @return float
 *   The required surface in cm2.
 */
function calculateCombustionChamberSurfaceRequirement(float $max_wood) {
  return 900 * $max_wood;
}

/**
 * Calculates max wood weight in kg (M'Fa).
 * 
 * @param float $power
 *   The nominal power of the oven.
 * @param float $heating_period
 *   The heating period.
 * 
 * @return float
 *   The max wood weight.
 */
function calculateMaxWood(float $power, float $heating_period) {
  return $power * $heating_period / (HA * MIN_EFFICIENCY);
}

/**
 * Calculates real density in kg/m3.
 * 
 * @param float $density
 *   Normal density in kg/Nm3 (normal cubic meter).
 * @param float $temp
 *   Temperature in Celsius.
 * @param float $alt
 *   Altitude above sea level in meter.
 * 
 * @return float
 *   The real density in kg/m3.
 */
function calculateRealDensity(float $density, float $temp, float $alt) {
  return $density / (calculateTempCorrectionFactor($temp) * calculateAltCorrectionFactor($alt));
}

function calculateTempCorrectionFactor(float $temp) {
  return (273 + $temp) / 273;
}

function calculateAltCorrectionFactor(float $alt) {
  return 1 / (exp(-9.81 * $alt / 78624));
}

/**
 * Calculates temperature drop in flue passage.
 * 
 * @param float $length
 *   The length of the passage.
 * @param float $minimal_length
 *   The minimal length of the passage.
 * @param float $temp
 *   The temperature entering the passage.
 * 
 * @return float
 *   The temperature drop at given length.
 */
/*
function calculateTempDrop(float $length, float $minimal_length, $temp = 550) {
  // 550 Celsius is the flue temperature when leaving the
  // combustion chamber.
  // Minimum exhaust temperature should be 240C for a given 78% efficiency (from example).
  return $temp * exp((-0.83 * $length) / $minimal_length);
}
*/
/*
function calculatePressure() {
  return $g * $height * (AIR_DENSITY - FLUE_GAS_DENSITY);
}
*/
